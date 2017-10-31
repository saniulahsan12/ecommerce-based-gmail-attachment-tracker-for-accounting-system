<?php
include 'helpers.php';
include 'simple_html_dom/simple_html_dom.php';

if( isset($_POST['submit-date']) && !empty($_POST['date']) ):

    $directory      = "uploads/".date('Y/m/d', strtotime($_POST['date']))."/";
    $files          = scandir($directory);
    $output         = null;

    if( !empty($files) ):

        foreach($files as $file):

            $extracted      = array();
            $matches        = array();
            $tops           = array();
            $balancer       = array();
            $formatter      = null;
            $splinter       = ' ';

            if($file == '.' || $file == '..'):
                continue;
            endif;

            copy($directory.$file, $file);

            $processdir = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);

            $command    = "-f 1 $file ".$processdir;

            exec("pdftohtml.exe $command", $buffer, $return);

            // Create DOM from URL or file
            $html = @file_get_html($processdir.'/page1.html');

            if( empty($html) ) continue;
            // Find all div with text
            foreach($html->find('div.txt') as $element):
                $extracted[] = $element->outertext;
            endforeach;

            // find all top values
            foreach($extracted as $element):
                preg_match('/(top:)(\d+)(px;)/', $element, $matches);
                if( !empty($matches) ):
                    $tops[] = $matches[0];
                endif;
            endforeach;

            $tops = array_unique($tops);

            // put same top values to same array or maintaining the format
            foreach($tops as $top):

                foreach($extracted as $element):
                    if(strpos($element, $top) !== false):
                        $formatter    .= $element.$splinter;
                    endif;
                endforeach;

                $balancer[]    = $formatter;
                $formatter     = '';

            endforeach;

            // removing unwanted characaters and tags from the string
            foreach($balancer as $key => $plain):
                $balancer[$key] = strip_tags($plain);
            endforeach;

            $output .= '<div class="alert alert-info">';
            $output .= '<h4>File ID:'.$file.'</h4>';
            // $output .= '<h4>Customer Number:'.customer_number($balancer).'</h4>');
            $output .= '<h4>Company Number:'.company_number($balancer).'</h4>';
            $output .= '<h4>Vat Data:'.vat_calculate($balancer).'</h4>';
            $output .= '<h4>Total Data:'.total_calculate($balancer).'</h4>';
            $output .= '</div>';

            deleteDirectory($file);
            deleteDirectory($processdir);

        endforeach;

    endif;
endif;

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport"
     content="width=device-width, initial-scale=1, user-scalable=yes">
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
      <div class="col-sm-offset-2 col-sm-10">
          <h3>Choose date and extract attachment to database</h3>
      </div>
      <form class="form-horizontal" action="" method="post">
        <div class="form-group">
          <label class="control-label col-sm-2" for="email">Date:</label>
          <div class="col-sm-10">
            <input type="date" class="form-control" id="email" placeholder="Enter email" name="date">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-success" type="submit" class="btn btn-default" name="submit-date">Submit</button>
          </div>
        </div>
      </form>

      <div class="col-sm-offset-2 col-sm-10">
          <?php
          if(!empty($output)):
             echo $output;
          endif;
         ?>
     </div>


    </div>
</body>
</html>
