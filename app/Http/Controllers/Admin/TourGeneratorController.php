<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TourController;
use App\Models\TourPackage;
use App\Support\TourGenerator\PackageGenerator;
use App\Support\TourGenerator\TourDocuments;
use App\Support\TourGenerator\TourImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class TourGeneratorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->routeIs('admin.generator.imports')) {
            return view('admin.generator.index', [
                'importPage' => true,
                'imports' => DB::table('tour_gen_import_batches as batch')->leftJoin('users', 'users.id', '=', 'batch.uploaded_by')
                    ->select('batch.*', 'users.name as uploader')->orderByDesc('batch.id')->paginate(15),
                'tours' => DB::table('tour_packages')->where('status', 'published')->orderBy('title')->get(['id', 'title']),
            ]);
        }
        return view('admin.generator.index', [
            'importPage' => false,
            'locations' => DB::table('tour_gen_locations')->where('status', 'approved')->orderBy('name')->get(),
            'destinations' => DB::table('destinations')->where('status', 'published')->orderBy('name')->get(),
            'categories' => DB::table('tour_categories')->where('is_active', true)->orderBy('name')->get(),
            'activities' => DB::table('tour_gen_activities')->where('status', 'approved')->orderBy('name')->get(),
            'runs' => DB::table('tour_gen_generation_runs as run')->leftJoin('users', 'users.id', '=', 'run.requested_by')
                ->select('run.*', 'users.name as requester')->orderByDesc('run.id')->paginate(15, pageName: 'runs'),
            'approvedImports' => DB::table('tour_gen_import_batches')->where('status', 'approved')->latest('id')->get(['id', 'filename']),
        ]);
    }

    public function generate(Request $request, PackageGenerator $generator)
    {
        $filters = $request->validate([
            'source' => 'required|in:database,import,hybrid', 'count' => 'required|integer|between:1,100',
            'min_days' => 'required|integer|between:1,30', 'max_days' => 'required|integer|between:1,30|gte:min_days',
            'destinations' => 'nullable|array|max:20', 'destinations.*' => 'integer|exists:destinations,id',
            'category' => 'nullable|integer|exists:tour_categories,id', 'country' => 'nullable|regex:/^[A-Z]{2}$/',
            'start' => 'nullable|integer|exists:tour_gen_locations,id', 'end' => 'nullable|integer|exists:tour_gen_locations,id',
            'activity' => 'nullable|integer|exists:tour_gen_activities,id', 'tier' => 'nullable|in:budget,mid_range,luxury',
            'similarity' => 'required|integer|between:70,100',
            'import_batch' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('tour_gen_import_batches', 'id')->where('status', 'approved')],
            'import_batches' => ['nullable', 'array', 'max:100'],
            'import_batches.*' => ['integer', \Illuminate\Validation\Rule::exists('tour_gen_import_batches', 'id')->where('status', 'approved')],
            'complete_packages' => ['nullable', 'boolean'],
        ]) + ['destinations' => []];
        $id = DB::table('tour_gen_generation_runs')->insertGetId([
            'requested_by' => $request->user()->id, 'requested_count' => $filters['count'],
            'status' => 'running', 'filters' => json_encode($filters), 'created_at' => now(), 'updated_at' => now(),
        ]);
        try {
            $generator->run($id, $filters);
        } catch (Throwable $error) {
            report($error);
            DB::table('tour_gen_generation_runs')->where('id', $id)->update(['status' => 'failed', 'updated_at' => now()]);
        }
        Log::info('Tour generation finished', ['run_id' => $id, 'user_id' => $request->user()->id]);
        return redirect()->route('admin.generator.run', $id);
    }

    public function run(int $run)
    {
        $record = DB::table('tour_gen_generation_runs')->find($run);
        abort_unless($record, 404);
        return view('admin.generator.run', [
            'run' => $record, 'metrics' => json_decode($record->metrics ?? '{}', true),
            'packages' => TourPackage::with(['destination', 'categories'])->whereIn('id', DB::table('tour_gen_run_results')
                ->where('run_id', $run)->select('tour_package_id'))->orderBy('id')->get(),
        ]);
    }

    public function template(string $format, TourDocuments $documents)
    {
        abort_unless(in_array($format, ['docx', 'xlsx'], true), 404);
        if (!class_exists(\ZipArchive::class)) {
            return back()->withErrors(['template' => 'Enable the PHP zip extension to download Office templates.']);
        }
        return response($documents->download($format), 200, [
            'Content-Disposition' => 'attachment; filename="Serenovia-Tour-Template.'.$format.'"',
            'Content-Type' => $format === 'xlsx' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function upload(Request $request, TourDocuments $documents, TourImport $import)
    {
        $request->validate(['document' => 'required|file|max:5120|extensions:csv,xlsx,docx,pdf|mimes:csv,txt,xlsx,docx,pdf,zip']);
        $file = $request->file('document');
        $path = $file->getRealPath();
        $batch = $this->newBatch($request, $file->getClientOriginalName(), hash_file('sha256', $path));
        try {
            $this->stage($batch, $documents->read($path, strtolower($file->getClientOriginalExtension())), $import);
        } catch (RuntimeException $error) {
            DB::table('tour_gen_import_batches')->where('id', $batch)->update(['status' => 'failed', 'error' => $error->getMessage()]);
        } catch (Throwable $error) {
            report($error);
            DB::table('tour_gen_import_batches')->where('id', $batch)->update(['status' => 'failed', 'error' => 'The document could not be processed.']);
        }
        return redirect()->route('admin.generator.review', $batch);
    }

    public function fromTour(Request $request, TourImport $import)
    {
        $request->validate(['source_tour' => 'required|integer']);
        $tour = TourPackage::where('status', 'published')->findOrFail($request->integer('source_tour'));
        $days = $tour->itinerary_days ?: \App\Support\SafariDays::sections($tour);
        if (!$days) {
            throw ValidationException::withMessages(['tour' => 'This tour has no itinerary days.']);
        }
        $tour->load(['destination', 'categories', 'accommodations']);
        $rows = [];
        foreach ($days as $index => $day) {
            $number = $index + 1;
            $end = $day['location'] ?? '';
            $stay = $tour->accommodations->first(fn ($item) => (int) $item->pivot->day_number === $number);
            $rows['Day '.$number] = [
                'tour_ref' => 'TOUR-'.$tour->id, 'day_number' => $number, 'day_title' => $day['title'] ?? '',
                'tour_type' => $tour->tour_type, 'destination_id' => $tour->destination_id,
                'category_id' => $tour->categories->first()?->id, 'description' => $day['details'] ?? strip_tags($day['html'] ?? ''),
                'country_code' => ['Tanzania' => 'TZ', 'Kenya' => 'KE', 'Uganda' => 'UG', 'Rwanda' => 'RW'][$tour->destination?->country] ?? '',
                'start' => $index === 0 ? $tour->starting_point : ($days[$index - 1]['location'] ?? ''),
                'end' => $end ?: ($number === count($days) ? $tour->ending_point : ''),
                'overnight' => $stay?->location, 'accommodation_id' => $stay?->id,
                '_tour_id' => $tour->id, '_day_key' => $day['id'] ?? null,
                'transport_gallery_ids' => implode('|', $day['transport_gallery_ids'] ?? []),
            ] + array_intersect_key($day, array_flip(['meals', 'distance', 'walking_time', 'altitude', 'latitude', 'longitude']));
        }
        $batch = $this->newBatch($request, $tour->title, hash('sha256', json_encode($rows)));
        $this->stage($batch, $rows, $import);
        return redirect()->route('admin.generator.review', $batch);
    }

    public function review(Request $request, int $batch)
    {
        $record = DB::table('tour_gen_import_batches')->find($batch);
        abort_unless($record && ((int) $record->uploaded_by === (int) $request->user()->id || $record->status === 'approved'), 404);
        return view('admin.generator.review', [
            'batch' => $record, 'fields' => TourDocuments::FIELDS,
            'photoOptions' => \App\Models\GalleryImage::orderBy('category')->orderBy('title')->get(),
            'records' => DB::table('tour_gen_import_records')->where('batch_id', $batch)->orderBy('id')->paginate(10),
            'destinations' => DB::table('destinations')->orderBy('name')->get(['id', 'name']),
            'accommodations' => DB::table('accommodations')->where('status', 'published')->orderBy('name')->get(['id', 'name']),
            'categories' => DB::table('tour_categories')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function correct(Request $request, int $batch, int $record, TourImport $import)
    {
        $data = $request->validate(['fields' => 'required|array', 'fields.*' => 'nullable|string|max:12000', 'skip' => 'nullable|boolean']);
        $row = array_map(fn ($value) => trim((string) $value), array_intersect_key($data['fields'], array_flip(TourDocuments::FIELDS)));
        if ($request->boolean('photo_picker')) {
            $photos = $request->validate([
                'photo_ids' => 'nullable|array|max:5', 'photo_ids.*' => 'integer',
                'transport_ids' => 'nullable|array|max:5', 'transport_ids.*' => 'integer',
            ]);
            $row['gallery_ids'] = implode('|', $photos['photo_ids'] ?? []);
            $row['transport_gallery_ids'] = implode('|', $photos['transport_ids'] ?? []);
        }
        $errors = $import->errors($row);
        DB::transaction(function () use ($request, $batch, $record, $row, $errors) {
            $claimed = DB::table('tour_gen_import_batches')->where('id', $batch)->where('uploaded_by', $request->user()->id)
                ->where('status', 'review')->update(['updated_at' => now()]);
            abort_unless($claimed, 404);
            $source = DB::table('tour_gen_import_records')->where('batch_id', $batch)->find($record);
            abort_unless($source, 404);
            $metadata = array_intersect_key(json_decode($source->raw_payload, true), array_flip(['_tour_id', '_day_key']));
            DB::table('tour_gen_import_records')->where('id', $record)->update([
                'mapped_payload' => json_encode($row + $metadata), 'validation_messages' => json_encode($errors),
                'status' => $request->boolean('skip') ? 'skipped' : ($errors ? 'invalid' : 'ready'), 'updated_at' => now(),
            ]);
        });
        if ($request->boolean('preview') && !$request->boolean('skip')) {
            return redirect()->route('admin.generator.import-preview', [$batch, $record]);
        }
        return back()->with('success', 'Mapping saved.');
    }

    public function approve(Request $request, int $batch, TourImport $import)
    {
        abort_unless(DB::table('tour_gen_import_batches')->where('id', $batch)->where('uploaded_by', $request->user()->id)->exists(), 404);
        $import->approve($batch, $request->user()->id);
        Log::info('Tour import approved', ['batch_id' => $batch, 'user_id' => $request->user()->id]);
        return back()->with('success', 'Approved itinerary blocks are available to the generator.');
    }

    public function preview(TourPackage $tour, TourController $controller)
    {
        abort_unless(DB::table('tour_gen_run_results')->where('tour_package_id', $tour->id)->exists(), 404);
        $url = route('admin.generator.preview', $tour);
        if (!request()->boolean('frame')) {
            return view('admin.generator.preview', [
                'title' => $tour->title, 'url' => $url, 'edit' => route('admin.tours.edit', $tour),
                'back' => route('admin.generator.index'),
            ]);
        }
        return $this->publicPreview($controller, $tour, $url);
    }

    public function importPreview(Request $request, int $batch, int $record, TourController $controller)
    {
        $source = DB::table('tour_gen_import_batches')->find($batch);
        abort_unless($source && ((int) $source->uploaded_by === (int) $request->user()->id || $source->status === 'approved'), 404);
        $selected = DB::table('tour_gen_import_records')->where('batch_id', $batch)->where('status', '!=', 'skipped')->find($record);
        abort_unless($selected, 404);
        $row = json_decode($selected->mapped_payload, true);
        $reference = $row['tour_ref'] ?? '';
        $rows = $reference === '' ? collect([$row]) : DB::table('tour_gen_import_records')->where('batch_id', $batch)
            ->where('status', '!=', 'skipped')->orderBy('id')->pluck('mapped_payload')
            ->map(fn ($payload) => json_decode($payload, true))->filter(fn ($day) => ($day['tour_ref'] ?? '') === $reference)
            ->sortBy(fn ($day) => (int) ($day['day_number'] ?? 0))->values();
        $first = $rows->first();
        $url = route('admin.generator.import-preview', [$batch, $record]);
        $title = ($first['tour_title'] ?? '') ?: (($first['day_title'] ?? '') ?: 'Imported itinerary');
        if (!$request->boolean('frame')) {
            $page = (int) ceil(DB::table('tour_gen_import_records')->where('batch_id', $batch)->where('id', '<=', $record)->count() / 10);
            $review = route('admin.generator.review', ['batch' => $batch, 'page' => $page]);
            return view('admin.generator.preview', [
                'title' => $title, 'url' => $url, 'back' => $review,
                'edit' => $source->status === 'review' ? $review.'#record-'.$record : null,
            ]);
        }
        $gallery = \App\Models\GalleryImage::whereIn('id', $rows->flatMap(fn ($day) => preg_split('/\s*\|\s*/', $day['gallery_ids'] ?? '', -1, PREG_SPLIT_NO_EMPTY))->unique())->pluck('path', 'id');
        $tour = new TourPackage([
            'title' => $title, 'tour_type' => ($first['tour_type'] ?? '') ?: 'safari',
            'destination_id' => ($first['destination_id'] ?? '') ?: null,
            'duration_days' => $rows->count(), 'duration_nights' => $rows->filter(fn ($day) => !empty($day['overnight']))->count(),
            'starting_point' => $first['start'] ?? null, 'ending_point' => $rows->last()['end'] ?? null,
            'description' => $first['overview'] ?? null, 'inclusions' => $first['inclusions'] ?? null,
            'exclusions' => $first['exclusions'] ?? null, 'currency' => 'USD', 'status' => 'draft',
            'itinerary_days' => $rows->map(fn ($day) => [
                'title' => $day['day_title'] ?? '', 'details' => $day['description'] ?? '', 'location' => $day['end'] ?? '',
                'images' => collect(explode('|', $day['gallery_ids'] ?? ''))->map(fn ($id) => $gallery->get(trim($id)))->filter()->values()->all(),
                'transport_gallery_ids' => array_filter(array_map('trim', explode('|', $day['transport_gallery_ids'] ?? '')), 'ctype_digit'),
            ] + array_intersect_key($day, array_flip(['meals','altitude','distance','walking_time','latitude','longitude'])))->all(),
        ]);
        // The public view builds contact URLs with a tour ID. This model is never persisted.
        $tour->id = 0;
        $stays = $rows->map(function ($day, $index) {
            $stay = \App\Models\Accommodation::with('destination')->find(($day['accommodation_id'] ?? '') ?: 0);
            if ($stay) {
                $stay->setRelation('pivot', (object) ['day_number' => $index + 1, 'tier' => $stay->tier]);
            }
            return $stay;
        })->filter()->values();
        $tour->setRelation('accommodations', $stays);
        $tour->setRelation('categories', \App\Models\TourCategory::whereIn('id', $rows->pluck('category_id')->filter()->unique())->get());
        return $this->publicPreview($controller, $tour, $url);
    }

    private function publicPreview(TourController $controller, TourPackage $tour, string $url)
    {
        return response($controller->details($tour, true)->with('previewBaseUrl', $url))
            ->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }

    private function newBatch(Request $request, string $filename, string $hash): int
    {
        return DB::table('tour_gen_import_batches')->insertGetId([
            'uploaded_by' => $request->user()->id, 'filename' => mb_substr($filename, 0, 255),
            'sha256' => $hash, 'status' => 'processing', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function stage(int $batch, array $rows, TourImport $import): void
    {
        DB::transaction(function () use ($batch, $rows, $import) {
            foreach ($rows as $source => $row) {
                $errors = $import->errors($row);
                DB::table('tour_gen_import_records')->insert([
                    'batch_id' => $batch, 'source_locator' => $source, 'raw_payload' => json_encode($row, JSON_THROW_ON_ERROR),
                    'mapped_payload' => json_encode($row, JSON_THROW_ON_ERROR), 'validation_messages' => json_encode($errors),
                    'status' => $errors ? 'invalid' : 'ready', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('tour_gen_import_batches')->where('id', $batch)->update(['status' => 'review', 'updated_at' => now()]);
        });
    }
}
