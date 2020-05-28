jQuery(function ($) {

    let search_form = $("#AmazonImages_search_form");
    let search_results = $('#AmazonImages_search_results');
    let current_locale = search_form.find('[name="locale"]').val();

    if (!current_locale || current_locale === 'com') {
        addOptions(search_form.find('[name="search_index"]'), getSearchIndexesByLocale(current_locale));
    }

    $('[data-settings-test-response]').empty().hide(); // clear credentials test response block.

    function addOptions(selector, options) {
        if (selector.length === 0) {
            return false;
        }
        selector.empty();
        $.each(options, function (index, value) {
            selector.append('<option value="' + value + '">' + value + '</option>');
        })
    }

    setTimeout(function() {
        if($('.edit-post-header-toolbar').length == 1) {
            $('.edit-post-header-toolbar').append($('#insert-amazon-images-button'));
        }
    }, 1000);

    search_results
        .on('click', '[data-images] img', function (e) {
            $(this).closest('tbody').find('[data-primary-image]')
                .prop('src', $(this).data('medium-image'))
                .data('small-image', $(this).prop('src'))
                .data('large-image', $(this).data('large-image'));
        })
        // insert image into the post
        .on('click', "button", function (e) {
            let image_size = $(this).closest('tbody').find('input[name="selected_image"]:checked').val();
            let image_url = null;
            switch (image_size) {
                case 'small':
                    image_url = $(this).closest('tbody').find('[data-primary-image]').data('small-image');
                    break;
                case 'medium':
                    image_url = $(this).closest('tbody').find('[data-primary-image]').prop('src');
                    break;
                case 'large':
                default:
                    image_url = $(this).closest('tbody').find('[data-primary-image]').data('large-image');
            }
            let title = $(this).closest('tbody').find('[data-url]').prop('title');

            let use_nofollow = ($('#AmazonImages_link_template').data('use-nofollow') === 'yes'); // check nofollow config attribute

            // use link template
            let image_html_template = $('#AmazonImages_link_template').prop('content').cloneNode(true);
            $(image_html_template).find('a').prop('title', title);
            $(image_html_template).find('a').prop('href', $(this).closest('tbody').find('[data-url]').prop('href'));
            if (use_nofollow) {
                $(image_html_template).find('a').prop('rel', 'nofollow');
            }

            $(image_html_template).find('img').prop('title', title);
            $(image_html_template).find('img').prop('src', image_url);
            $(image_html_template).find('img').prop('alt', title);
            $(image_html_template).find('img').prop('class', 'size-' + image_size + ' aligncenter');

            image_html_template = $('<div></div>').append($(image_html_template)).html();

            let tve_image_html_template = $('#AmazonImages_link_template').prop('content').cloneNode(true);
            $(tve_image_html_template).find('a').prop('title', title);
            $(tve_image_html_template).find('a').prop('href', $(this).closest('tbody').find('[data-url]').prop('href'));
            if (use_nofollow) {
                $(tve_image_html_template).find('a').prop('rel', 'nofollow');
            }

            $(tve_image_html_template).find('img').prop('title', title);
            $(tve_image_html_template).find('img').prop('src', image_url);
            $(tve_image_html_template).find('img').prop('alt', title);
            $(tve_image_html_template).find('img').prop('class', 'tve_image');
            tve_image_html_template = $('<div></div>').append($(tve_image_html_template)).html();

            let editor = null;
            if ($(this).data('type') === 'iframe') {
                editor = window.parent;
            }
            else {
                editor = window;
            }

            let tve_image_frame = '<div class="thrv_wrapper tve_image_caption tve-draggable tve-droppable edit_mode on_hover" ' +
                'draggable="true"><span class="tve_image_frame" style="width: 100%">' + tve_image_html_template + '</span></div>';
            let tve_editor = $('#tve-editor-frame', editor.document).contents().find('#tve_editor');
            let block_editor = $('.block-editor');
            if (tve_editor.length > 0) {
                tve_editor.find('.tcb-elem-placeholder').replaceWith(tve_image_frame);
            }
            else if (block_editor.length > 0) {
                let block = wp.blocks.createBlock( 'core/paragraph', { content: image_html_template } );
                wp.data.dispatch( 'core/editor' ).insertBlocks( block );
            }
            else {
                editor.send_to_editor(image_html_template);
            }
            // If the old thickbox remove function exists, call it
            if (editor.tb_remove) {
                try {
                    editor.tb_remove();
                } catch (e) {
                }
            }
        });

    search_form
        .on('change', '[name="locale"]', function (e) {
            addOptions($(e.delegateTarget).find('[name="search_index"]'), getSearchIndexesByLocale($(this).val()));
        })
        .on('keyup', '[name="asin"]', function (e) {
            if ($(this).val().length > 0) {
                $(e.delegateTarget).find('[name="keyword"]').prop('disabled', true);
                $(e.delegateTarget).find('[name="search_index"]').prop('disabled', true);
            }
            else {
                $(e.delegateTarget).find('[name="keyword"]').prop('disabled', false);
                $(e.delegateTarget).find('[name="search_index"]').prop('disabled', false);
            }
        })
        .on('keyup', '[name="keyword"]', function (e) {
            if ($(this).val().length > 0) {
                $(e.delegateTarget).find('[name="asin"]').prop('disabled', true);
            }
            else {
                $(e.delegateTarget).find('[name="asin"]').prop('disabled', false);
            }
        })
        .on('reset', function (e) {
            $(e.delegateTarget).find(':disabled').prop('disabled', false);
            search_results
                .empty()
                .hide();
        })
        .on('submit', function (e) {
            e.preventDefault();
            search_results
                .empty()
                .hide();
            //let data=$(e.delegateTarget).serialize();
            let data = new FormData($(e.delegateTarget)[0]);
            data.append('action', 'search_form_submit');
            //'whatever': ajax_object.we_value      // We pass php values differently!
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            $.ajax({
                method: 'post',
                url: ajax_object.ajax_url,
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false
            })
                .done(function (data, status) {
                    search_results.show();
                    $.each(data, function (index, value) {
                        let template = $('#AmazonImages_search_result_template').prop('content').cloneNode(true);
                        $(template).find('form')
                            .prop('id', 'result_' + index)
                            .data('asin', value.asin);
                        $(template).find('[data-title]').text(value.title);
                        $(template).find('[data-primary-image]')
                            .prop('src', value.medium_image_url)
                            .prop('title', value.title)
                            .prop('alt', value.title)
                            .data('small-image', value.small_image_url)
                            .data('large-image', value.large_image_url);
                        $(template)
                            .find('form[id="result_' + index + '"]')
                            .find('input[name="selected_image"][value="medium"]')
                            .prop('checked', true);

                        $(template).find('[data-url]')
                            .prop('href', 'https://www.amazon.' + value.locale + '/dp/' + value.asin + '/?tag=' + value.associate_tag)
                            .prop('title', value.title);
                        if (value.sale_price) {
                            $(template).find('[data-price]').text(value.sale_price + ' ' + value.currency);
                        }
                        else if(value.price) {
                            $(template).find('[data-price]').text(value.price + ' ' + value.currency);
                        }
                        else{
                            $(template).find('[data-price]').text('not found.');
                        }


                        // add additional images
                        if (value.images.small.length > 0) {
                            $.each(value.images.small, function (index2, value2) {
                                let template_image = $('#AmazonImages_search_result_image_template').prop('content').cloneNode(true);
                                $(template_image).find('img')
                                    .prop('src', value2)
                                    .data('medium-image', value.images.medium[index2])
                                    .data('large-image', value.images.large[index2]);
                                $(template).find('[data-images]').append($(template_image));
                            });
                        }
                        else {
                            $(template).find('[data-images]').text('No additional images found.');
                        }
                        search_results.append($(template));
                    })
                })
                .fail(function (xhr, status, error) {
                    search_results.show();
                    let template = $('#AmazonImages_search_result_error_template').prop('content').cloneNode(true);
                    $(template).find('[data-error]').text(xhr.responseText);
                    search_results.append($(template));
                });

        });

    $('#insert-amazon-images-button').on('click', function (e) {
        search_form.trigger('reset');
        search_results.empty().hide();
        setTimeout(function () {
            $('#TB_ajaxContent')
                .css('width', $('#TB_window').width() - 30)
                .css('height', $('#TB_window').height() - 52);
        }, 5);
    });

    $(window).on('resize', function () {
        setTimeout(function () {
            $('#TB_ajaxContent')
                .css('width', $('#TB_window').width() - 30)
                .css('height', $('#TB_window').height() - 52);
        }, 5);
    });

    // credentials test button click
    $('[data-settings-tag]').on('click', 'button', function (e) {
        $('[data-settings-test-response]').empty().hide(); // clear credentials test response block.
        let button = $(this);
        let locale = button.closest('td').data('settings-tag');
        button.prop('disabled', true);
        let tag = button.closest('td').children('input').val();
        let access_key = button.closest('table').find('input[name$="AccessKey"]').val();
        let secret_key = button.closest('table').find('input[name$="SecretKey"]').val();
        let data = new FormData();
        data.append('action', 'credentials_test');
        data.append('locale', locale);
        data.append('tag', tag);
        data.append('access_key', access_key);
        data.append('secret_key', secret_key);
        $.ajax({
            method: 'post',
            url: ajax_object.ajax_url,
            data: data,
            dataType: 'json',
            contentType: false,
            processData: false
        })
            .done(function (data, status) {
                $('[data-settings-test-response]')
                    .empty()
                    .removeClass()
                    .addClass('updated')
                    .append('<p>' + data + '</p>')
                    .show();
                button.prop('disabled', false);
            })
            .fail(function (xhr, status, error) {
                $('[data-settings-test-response]')
                    .empty()
                    .removeClass()
                    .addClass('error')
                    .append('<p>' + ((xhr.responseText != 0) ? xhr.responseText : error) + '</p>')
                    .show();
                button.prop('disabled', false);
            });

    })
});

function numberWithCommas(x) {
    if (!x) return x;
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

String.prototype.capitalizeFirstLetter = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};


function getSearchIndexesByLocale(locale) {
    if (!locale) {
        return [];
    }
    switch (locale) {
        case 'com':
            return [
                'All',
                'Appliances',
                'ArtsAndCrafts',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'Collectibles',
                'Electronics',
                'Fashion',
                'FashionBaby',
                'FashionBoys',
                'FashionGirls',
                'FashionMen',
                'FashionWomen',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'HomeGarden',
                'Industrial',
                'KindleStore',
                'LawnAndGarden',
                'Luggage',
                'Magazines',
                'MobileApps',
                'Movies',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'Pantry',
                'PCHardware',
                'PetSupplies',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'UnboxVideo',
                'Vehicles',
                'VideoGames',
                'Wine',
                'Wireless'
            ];
        case 'de':
            return [
                'All',
                'Apparel',
                'Appliances',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'Classical',
                'DVD',
                'Electronics',
                'ForeignBooks',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'HomeGarden',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'Lighting',
                'Luggage',
                'Magazines',
                'MobileApps',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'Pantry',
                'PCHardware',
                'PetSupplies',
                'Photo',
                'Shoes',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'UnboxVideo',
                'VideoGames',
                'Watches'
            ];
        case 'ca':
            return [
                'All',
                'Apparel',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'DVD',
                'Electronics',
                'GiftCards',
                'Grocery',
                'HealthPersonalCare',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'LawnAndGarden',
                'Luggage',
                'MobileApps',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'PetSupplies',
                'Shoes',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'VideoGames',
                'Watches'
            ];
        case 'it':
            return [
                'All',
                'Apparel',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'DVD',
                'Electronics',
                'ForeignBooks',
                'Garden',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'Lighting',
                'Luggage',
                'MobileApps',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'PCHardware',
                'Shoes',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'VideoGames',
                'Watches'
            ];
        case 'es':
            return [
                'All',
                'Apparel',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'DVD',
                'Electronics',
                'ForeignBooks',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'LawnAndGarden',
                'Lighting',
                'Luggage',
                'MobileApps',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'PCHardware',
                'Shoes',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'VideoGames',
                'Watches'
            ];
        case 'fr':
            return [
                'All',
                'Apparel',
                'Appliances',
                'Baby',
                'Beauty',
                'Books',
                'Classical',
                'DVD',
                'Electronics',
                'ForeignBooks',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'HomeImprovement',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'LawnAndGarden',
                'Lighting',
                'Luggage',
                'MobileApps',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'PCHardware',
                'PetSupplies',
                'Shoes',
                'Software',
                'SportingGoods',
                'Toys',
                'VideoGames',
                'Watches'
            ];
        case 'co.uk':
            return [
                'All',
                'Apparel',
                'Appliances',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'Classical',
                'DVD',
                'Electronics',
                'GiftCards',
                'Grocery',
                'Handmade',
                'HealthPersonalCare',
                'HomeGarden',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'Kitchen',
                'Lighting',
                'Luggage',
                'MobileApps',
                'MP3Downloads',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'Pantry',
                'PCHardware',
                'PetSupplies',
                'Shoes',
                'Software',
                'SportingGoods',
                'Tools',
                'Toys',
                'UnboxVideo',
                'VHS',
                'VideoGames',
                'Watches'
            ];
        case 'in':
            return [
                'All',
                'Appliances',
                'Automotive',
                'Baby',
                'Beauty',
                'Books',
                'DVD',
                'Electronics',
                'Furniture',
                'GiftCards',
                'Grocery',
                'HealthPersonalCare',
                'HomeGarden',
                'Industrial',
                'Jewelry',
                'KindleStore',
                'LawnAndGarden',
                'Luggage',
                'LuxuryBeauty',
                'Music',
                'MusicalInstruments',
                'OfficeProducts',
                'Pantry',
                'PCHardware',
                'PetSupplies',
                'Shoes',
                'Software',
                'SportingGoods',
                'Toys',
                'VideoGames',
                'Watches'
            ];
        case 'com.au':
            return [
                'All',
                'Baby',
                'Beauty',
                'KindleStore',
                'Electronics',
                'Fashion',
                'HealthPersonalCare',
                'MobileApps',
                'Movies',
                'OfficeProducts',
                'PCHardware',
                'Music',
                'SportingGoods',
                'Software',
                'Books',
                'Toys',
                'VideoGames'
            ];
        default:
            return [];
    }
}

/**
 *
 * @param text
 * @returns {*}
 */
function escapeHtml(text) {
    if (!text) return text;
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function (m) {
        return map[m];
    });
}

