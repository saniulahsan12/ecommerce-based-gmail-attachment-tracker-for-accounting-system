<?php
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function string_search($to_find, $given_sentence) {
    foreach ($given_sentence as $url):
        if (strpos($to_find, $url) !== FALSE):
            return $given_sentence;
        endif;
    endforeach;
    return;
}

function total_calculate($value_array) {

    $suggestion = array('םולשתל כ"הס', 'םולשתל הרתי','יללכ כ"הס קיטסלפ טגמא תולעבב ירתונו ניה וז תינובשחב יטרופמה יבוטה', 'כ"הס','ח"ש');
    // $suggestion = array('םולשתל כ"הס', 'םולשתל הרתי','יללכ כ"הס קיטסלפ טגמא תולעבב ירתונו ניה וז תינובשחב יטרופמה יבוטה', 'כ"הס');

    foreach($value_array as $value):
        foreach($suggestion as $guess):
            if(strpos($value, $guess) !== false):
                preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $value, $matches);
                if(!empty($matches)):
                    $data_set[] = $value;
                endif;
            endif;
        endforeach;
    endforeach;

    if(!empty($data_set)):
        foreach($data_set as $single_entity):
            $collection[] = explode(' ', $single_entity);
        endforeach;
    endif;

    if(!empty($collection)):
        foreach($collection as $data):
            foreach($data as $value):
                $the_number = str_replace(array(','), '', $value);
                $the_number = ( strlen(explode('.', $the_number)[0]) <= 8 ) ? $the_number : 0; // 8 is for removing very big number. like 10 or 11 digit. they are not amount , they are invoice number or something else.
                $the_number = (double)$the_number;
                $mod_val[]  = is_float($the_number) ? $the_number : 0;
            endforeach;
        endforeach;
        return max($mod_val);
    endif;

    return;
}

// ח.פ ---->>>> company number track
// ח.פ. ---->>>> company number track
// ח.פ ---->>>> company number track

function vat_calculate($value_array) {

    // var_dump($value_array);

    $suggestion = array('%', '17% מ"עמ');

    foreach($value_array as $value):
        foreach($suggestion as $guess):
            if(strpos($value, $guess) !== false):
                preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $value, $matches);
                if(!empty($matches)):
                    $data_set[] = $value;
                endif;
            endif;
        endforeach;
    endforeach;

    if(!empty($data_set)):
        foreach($data_set as $single_entity):
            $collection[] = explode(' ', $single_entity);
        endforeach;
    endif;

    if(!empty($collection)):
        foreach($collection as $data):
            foreach($data as $value):
                $the_number = str_replace(array(','), '', $value);
                $the_number = ( strlen(explode('.', $the_number)[0]) <= 8 ) ? $the_number : 0; // 8 is for removing very big number. like 10 or 11 digit. they are not amount , they are invoice number or something else.
                $the_number = (double)$the_number;
                $mod_val[]  = is_float($the_number) ? $the_number : 0;
            endforeach;
        endforeach;
        return max($mod_val);
    endif;

    return;
}

function company_number($value_array) {

    $suggestion = array('פ.ח','פ.ח.', '.מ.ע', 'השרומ קסוע');

    foreach($value_array as $value):
        foreach($suggestion as $guess):
            if(strpos($value, $guess) !== false):
                preg_match('/\d{1,9}(,\d{3})*(\.\d+)?/', $value, $matches);
                if(!empty($matches)):
                    $data_set = explode(' ', $value);
                    $counter = 0;
                    foreach($data_set as $flat_values):
                        if( isset($data_set[$counter+1]) && in_array($data_set[$counter+1], $suggestion) ):
                            $data_set[$counter] = (double)str_replace(array('.'), '', $flat_values);
                        else:
                            $data_set[$counter] = (double)str_replace(array('.'), '', $flat_values);
                        endif;
                        $counter++;
                    endforeach;

                    return max($data_set);
                endif;
            endif;
        endforeach;
    endforeach;

    return;
}

function customer_number($value_array) {
    var_dump($value_array);
}
