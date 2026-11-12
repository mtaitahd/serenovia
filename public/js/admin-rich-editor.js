(() => {
    if (!window.tinymce) return;

    tinymce.init({
        selector: 'textarea.tinymce-editor',
        height: 360,
        menubar: 'file edit view insert format tools table help',
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount quickbars',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat | code | help',
        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre',
        quickbars_selection_toolbar: 'bold italic underline | blocks forecolor backcolor | link image',
        content_css: 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap',
        content_style: 'body { font-family:Nunito,Arial,sans-serif; font-size:16px; line-height:1.7; color:#26352b; } h1,h2,h3,h4,h5,h6 { margin-top:1.5em; margin-bottom:.55em; } ul,ol { margin-left:1.5em; } img { max-width:100%; height:auto; }',
        paste_data_images: false,
        branding: false,
        promotion: false,
        license_key: 'gpl',
        setup(editor) {
            editor.on('change input undo redo', () => editor.save());
        },
    });
})();
