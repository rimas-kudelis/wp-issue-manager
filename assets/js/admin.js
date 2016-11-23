jQuery(document).ready(function ($) {

    // Media/File upload button handler.
    // https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
    if ( typeof wp.media !== 'undefined' ) {
        var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;
        $('.issue-media-upload-button').click(function(e) {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            var id = button.attr('id').replace('_button', '');
            _custom_media = true;
                wp.media.editor.send.attachment = function(props, attachment){
                if ( _custom_media ) {
                    // Escaping the selector is so much easier with jQuery 3.0 and above.
                    // $("#" + $.escapeSelector(id)).val(attachment.url);
                    $("#" + id.replace( /(:|\.|\[|\]|,|=)/g, "\\$1" )).val(attachment.url);
                } else {
                    return _orig_send_attachment.apply( this, [props, attachment] );
                };
            }
            wp.media.editor.open(button);
            return false;
        });
        $('.add_media').on('click', function(){
            _custom_media = false;
        });
    }

    // Allow dynamically adding/removing and rearranging rows for Articles in an issue.
    var articlesMetaBox = $('#issue-articles');

    if(articlesMetaBox.length) {

        var articleList = $('.issue-article-list', articlesMetaBox),
            newArticleList = $('.issue-new-article', articlesMetaBox),
            newArticleId = newArticleList.data('new');

        var updateArticleIndices = function() {
            $('.issue-article', articleList).each(function(index, element) {
                var newId = 'issue_articles[' + index + ']';
                //$(element).attr('id', newId);
                $('.issue-article-order', element)
                    .attr('id', newId + '[order]')
                    .text(1 + index);
                $('.issue-article-author', element)
                    .attr('id', newId + '[author]')
                    .attr('name', newId + '[author]');
                $('.issue-article-title', element)
                    .attr('id', newId + '[title]')
                    .attr('name', newId + '[title]');
                $('.issue-article-url', element)
                    .attr('id', newId + '[url]')
                    .attr('name', newId + '[url]');
                $('.issue-article-url-button', element)
                    .attr('id', newId + '[url]_button')
                    .attr('name', newId + '[url]_button');
            });
        }

        $(articlesMetaBox).on('click', '.issue-add-article', function(){
            var newArticle = $('.issue-article', newArticleList).clone(true);
            articleList.append(newArticle);
            updateArticleIndices();
        });

        $(articlesMetaBox).on('click', '.issue-del-article', function(){
            var parent=$(this).parent();
            parent.remove();
            updateArticleIndices();
        });


        $(articleList).on( "sortstop", null, function( event, ui ) {
            updateArticleIndices();
        } );

        $(articleList).sortable({
            handle: ".issue-article-order",
            placeholder: "ui-state-highlight"
        });

    }

});