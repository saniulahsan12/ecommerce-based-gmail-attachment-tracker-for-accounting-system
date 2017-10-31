<?php
/*
ALL - return all messages matching the rest of the criteria
ANSWERED - match messages with the \\ANSWERED flag set
BCC "string" - match messages with "string" in the Bcc: field
BEFORE "date" - match messages with Date: before "date"
BODY "string" - match messages with "string" in the body of the message
CC "string" - match messages with "string" in the Cc: field
DELETED - match deleted messages
FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
FROM "string" - match messages with "string" in the From: field
KEYWORD "string" - match messages with "string" as a keyword
NEW - match new messages
OLD - match old messages
ON "date" - match messages with Date: matching "date"
RECENT - match messages with the \\RECENT flag set
SEEN - match messages that have been read (the \\SEEN flag is set)
SINCE "date" - match messages with Date: after "date"
SUBJECT "string" - match messages with "string" in the Subject:
TEXT "string" - match messages with text "string"
TO "string" - match messages with "string" in the To:
UNANSWERED - match messages that have not been answered
UNDELETED - match messages that are not deleted
UNFLAGGED - match messages that are not flagged
UNKEYWORD "string" - match messages that do not have the keyword "string"
UNSEEN - match messages which have not been read yet
*/

if( isset($_POST['submit-date']) && !empty($_POST['date']) ):

    $hostname     = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username     = 'saniul.cybernetikz@gmail.com';
    $password     = 'pbgvnuxabwafajqn';
    $max_emails   = 50;
    $directory    = "uploads/".date('Y/m/d', strtotime($_POST['date']))."/";
    $date         = date("d-M-Y", strtotime($_POST['date']));
    $query_string = "ON ".$date;

    if(!file_exists($directory)):
        mkdir($directory, 0777, true);
    endif;

    $fetch_log = "Fetching Mail For Date: $date ...";
    $start_log = "Script Started...";

    $inbox = imap_open($hostname,$username,$password) or die('Cannot Connect to Gmail: ' . imap_last_error());

    $emails = imap_search($inbox, $query_string);

    /* if any emails found, iterate through each email */
    if($emails) {

        $count = 1;

        /* put the newest emails on top */
        rsort($emails);

        /* for every email... */
        foreach($emails as $email_number)
        {

            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox,$email_number,0);

            /* get mail message */
            $message = imap_fetchbody($inbox,$email_number,2);

            /* get mail structure */
            $structure = imap_fetchstructure($inbox, $email_number);

            $attachments = array();

            /* if any attachments found... */
            if(isset($structure->parts) && count($structure->parts))
            {
                for($i = 0; $i < count($structure->parts); $i++)
                {
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );

                    if($structure->parts[$i]->ifdparameters)
                    {
                        foreach($structure->parts[$i]->dparameters as $object)
                        {
                            if(strtolower($object->attribute) == 'filename')
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }

                    if($structure->parts[$i]->ifparameters)
                    {
                        foreach($structure->parts[$i]->parameters as $object)
                        {
                            if(strtolower($object->attribute) == 'name')
                            {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }

                    if($attachments[$i]['is_attachment'])
                    {
                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

                        /* 4 = QUOTED-PRINTABLE encoding */
                        if($structure->parts[$i]->encoding == 3)
                        {
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        /* 3 = BASE64 encoding */
                        elseif($structure->parts[$i]->encoding == 4)
                        {
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }

            /* iterate through each attachment and save it */
            $count = 0;
            foreach($attachments as $attachment)
            {
                if($attachment['is_attachment'] == 1)
                {
                    $downloaded_file_name = $directory . $email_number . "-" . md5($attachment['name']) .".pdf";

                    $fp = fopen($downloaded_file_name, "w+");
                    fwrite($fp, $attachment['attachment']);
                    fclose($fp);

                    $files_downloaded_log[] = "<br>".$downloaded_file_name."<br>";
                }

            }

            if($count++ >= $max_emails) break;
        }

    }

    /* close the connection */
    imap_close($inbox);
    $end_log = "Script Ended...";

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
          <h3>Choose date and download attachment</h3>
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
          <?php if(!empty($start_log)): ?>
          <div class="alert alert-success">
              <?php echo $start_log; ?>
          </div>
         <?php endif; ?>

         <?php if(!empty($fetch_log)): ?>
         <div class="alert alert-success">
             <?php echo $fetch_log; ?>
         </div>
        <?php endif; ?>

        <?php if(!empty($files_downloaded_log)): ?>
        <div class="alert alert-info">
            <?php echo implode($files_downloaded_log); ?>
        </div>
       <?php endif; ?>

         <?php if(!empty($end_log)): ?>
         <div class="alert alert-success">
             <?php echo $end_log; ?>
         </div>
        <?php endif; ?>
     </div>


    </div>
</body>
</html>
