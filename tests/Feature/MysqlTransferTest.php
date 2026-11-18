<?php

namespace Tests\Feature;

use App\Support\Database\DatabaseBackup;
use App\Support\Database\SqliteToMysql;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Tests\TestCase;

class MysqlTransferTest extends TestCase
{
    private $target;
    private string $source;
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.transfer_test' => array_replace(config('database.connections.mysql'), [
            'database' => 'serenovia_transfer_testing', 'url' => null,
        ])]);
        $this->target = DB::connection('transfer_test');
        $this->target->getSchemaBuilder()->dropAllTables();
        $this->directory = storage_path('framework/testing/mysql-transfer-'.bin2hex(random_bytes(4)));
        mkdir($this->directory, 0700, true);
        $this->source = $this->directory.'/source.sqlite';
        $source = new PDO('sqlite:'.$this->source);
        $source->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $source->exec('CREATE TABLE tour_packages (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT, price DECIMAL, details JSON)');
        $source->exec("INSERT INTO users VALUES (7, 'Serenovia')");
        $insert = $source->prepare('INSERT INTO tour_packages VALUES (?, ?, ?, ?, ?)');
        $insert->execute([9, 7, "Safari's \\ trail\n🦁", '1234.50', '{"photos":[4,5],"day":{"title":"Mlima","id":2}}']);
    }

    protected function tearDown(): void
    {
        if ($this->target) {
            $this->target->getSchemaBuilder()->dropAllTables();
            DB::purge('transfer_test');
        }
        foreach (glob($this->directory.'/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function migrate(): void
    {
        $schema = $this->target->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        $schema->create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('title');
            $table->decimal('price', 12, 2)->nullable();
            $table->json('details')->nullable();
        });
    }

    public function test_import_preserves_values_relationships_and_auto_increment(): void
    {
        $counts = (new SqliteToMysql)->import($this->target, $this->source, fn () => $this->migrate());
        $this->assertSame(['tour_packages' => 1, 'users' => 1], $counts);
        $this->assertSame(7, $this->target->table('tour_packages')->value('user_id'));
        $this->assertSame("Safari's \\ trail\n🦁", $this->target->table('tour_packages')->value('title'));
        $this->assertSame('1234.50', $this->target->table('tour_packages')->value('price'));
        $this->assertSame([4, 5], json_decode($this->target->table('tour_packages')->value('details'), true)['photos']);
        $this->assertGreaterThan(7, $this->target->table('users')->insertGetId(['name' => 'Next']));
        $this->assertFileExists($this->source);
    }

    public function test_nonempty_destination_is_never_overwritten(): void
    {
        $this->migrate();
        $this->target->table('users')->insert(['id' => 20, 'name' => 'Keep me']);
        try {
            (new SqliteToMysql)->import($this->target, $this->source, fn () => $this->fail('Must not migrate'));
            $this->fail('Expected refusal');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('completely empty', $error->getMessage());
        }
        $this->assertSame('Keep me', $this->target->table('users')->value('name'));
    }

    public function test_orphan_rolls_back_and_restores_foreign_key_checks(): void
    {
        $source = new PDO('sqlite:'.$this->source);
        $source->exec('UPDATE tour_packages SET user_id=999');
        $source = null;
        try {
            (new SqliteToMysql)->import($this->target, $this->source, fn () => $this->migrate());
            $this->fail('Expected foreign-key rejection');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Foreign-key verification failed', $error->getMessage());
        }
        $this->assertSame(0, $this->target->table('users')->count());
        $this->assertSame(0, $this->target->table('tour_packages')->count());
        $this->assertSame(1, (int) $this->target->selectOne('SELECT @@FOREIGN_KEY_CHECKS AS enabled')->enabled);
    }

    public function test_sql_backup_restores_unicode_quotes_json_and_nulls(): void
    {
        (new SqliteToMysql)->import($this->target, $this->source, fn () => $this->migrate());
        $this->target->table('tour_packages')->insert(['id' => 15, 'title' => 'Empty values', 'user_id' => null, 'price' => null, 'details' => null]);
        $before = $this->target->table('tour_packages')->orderBy('id')->get()->toJson();
        $path = (new DatabaseBackup)->create($this->target, $this->directory);
        $this->assertFileExists($path);
        $this->assertStringNotContainsString('CREATE DATABASE', file_get_contents($path));
        $this->target->getSchemaBuilder()->dropAllTables();
        $this->target->unprepared(file_get_contents($path));
        $this->assertSame($before, $this->target->table('tour_packages')->orderBy('id')->get()->toJson());
        $this->assertSame(1, $this->target->table('users')->count());
    }
}
