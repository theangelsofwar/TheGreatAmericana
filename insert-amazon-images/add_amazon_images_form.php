    <form id="<?php echo $shortname?>_search_form" enctype="application/x-www-form-urlencoded">

        <table class="<?php echo $table_class;?>" cellspacing="0" cellpadding="0">
            <tr valign="top">
                <th scope="row"><label for="<?php echo $shortname?>_search_by_keyword">Search by keyword</label></th>
                <td><input style="width:100%" type="text" name="keyword" id="<?php echo $shortname?>_search_by_keyword" value=""/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $shortname?>_search_by_asin">Search by ASIN</label></th>
                <td><input style="width:100%" type="text" name="asin" size="10" maxlength="10" id="<?php echo $shortname?>_search_by_asin" value=""/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $shortname?>_locale">Search Locale</label></th>
                <td>
                    <select name="locale" style="width:100%" id="<?php echo $shortname?>_locale">
                        <option value="com" selected>US</option>
                        <option value="ca">CA</option>
                        <option value="de">DE</option>
                        <option value="co.uk">GB</option>
                        <option value="it">IT</option>
                        <option value="es">ES</option>
                        <option value="fr">FR</option>
                        <option value="in">IN</option>
                        <option value="com.au">AU</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $shortname?>_search_index">Search Index</label></th>
                <td>
                    <select name="search_index" style="width:100%" id="<?php echo $shortname?>_search_index">
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <td colspan="2">
                    <button type="submit" class="button button-primary">Search</button>
                    <button type="reset" class="button button-cancel">Reset</button>
                </td>
            </tr>

        </table>
    </form>
    <hr>
    <div style="display:none" id="<?php echo $shortname?>_search_results">

    </div>
