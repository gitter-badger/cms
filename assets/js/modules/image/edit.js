$(document).ready(function () {
    var aspectRatio;

    var imageSelector = $('#preview_image').imgAreaSelect({
        instance: true,
        //handles: true,
        onSelectEnd: function (img, selection) {
            $('input[name="crop_position"]').val(
                $('#preview_image').width() + ':' +
                $('#preview_image').height() + ':' +

                selection.width + ':' +
                selection.height + ':' +

                selection.x1 + ':' +
                selection.y1 + ':' +

                selection.x2 + ':' +
                selection.y2
            );
        }
    });


    $('select[name="thumbnail_type"]').change(
        function () {
            if ($(this).val() == 'square') {
                aspectRatio = '1:1';
            }
            else if ($(this).val() == 'thumb') {
                aspectRatio = '4:3';
            }
            else if ($(this).val() == 'portrait') {
                aspectRatio = '3:4';
            }

            imageSelector.setOptions({
                aspectRatio: aspectRatio
            });
        }).trigger('change');

    $(window).resize(function () {
        var selection = imageSelector.getSelection();

        $('input[name="crop_position"]').val(
            $('#preview_image').width() + ':' +
            $('#preview_image').height() + ':' +

            selection.width + ':' +
            selection.height + ':' +

            selection.x1 + ':' +
            selection.y1 + ':' +

            selection.x2 + ':' +
            selection.y2
        );
    });
});