<?php

/**
 * Class to handle notes
 */

class Note
{

  // Properties

  /**
  * @var int The Note ID from the database
  */
  public $id = null;

  /**
  * @var int When the Note was published
  */
  public $noteDate = null;

  /**
  * @var string The HTML content of the Note
  */
  public $noteText = null;

  /**
  * @var string The image of the Note
  */
  public $noteImage = null;

  /**
  * @var string The image of the Note
  */
  public $tweetId = null;

   /**
  * @var int Longitude
  */
  public $longitude = null;

   /**
  * @var int Latitude
  */
  public $latitude = null;

     /**
  * @var int temperature
  */
  public $temperature = null;

   /**
  * @var string weather icon
  */
  public $weatherIcon = null;

   /**
  * @var embed
  */
  public $embed = null;

   /**
  * @var string tags
  */
  public $noteTags = null;

   /**
  * @var string type
  */
  public $noteType = null;

  /**
 * @var boolean Twitter
 */
 public $twitter = null;

 /**
 * @var string Syndacation
 */
 public $syndication = null;

 /**
 * @var string RSVP
 */
 public $rsvp = null;

  /**
  * @var string Ping
  */
  public $ping = null;

  /**
  * @var string JSON Content
  */
  //public $jsonContent = null;

  /**
  * Sets the object's properties using the values in the supplied array
  *
  * @param assoc The property values
  */

  public function __construct( $data=array() ) {
    // Set ID
    if ( isset( $data['id'] ) ) $this->id = (int) $data['id'];

    // Micropub
    if ( isset( $data['content'] ) ) $this->noteText = $data['content'];
    if ( isset( $data['syndication'] ) ) $this->syndication = $data['syndication'];

    // Swarm and other external location-based posts
    // if ( isset( $data['latitude'] ) ) $this->latitude = $data['latitude'];
    // if ( isset( $data['longitude'] ) ) $this->longitude = $data['longitude'];
    if ( isset( $data['location'] ) ) $this->location = $data['location'];

    // Ordinary post
    if ( isset( $data['tweet_id'] ) ) $this->tweetId = (int) $data['tweet_id'];
    if ( isset( $data['twitter'] ) ) $this->twitter = (int) $data['twitter'];
    if ( isset( $data['note_date'] ) ) $this->noteDate = (int) $data['note_date'];
    if ( isset( $data['note_text'] ) ) $this->noteText = $data['note_text'];
    if ( isset( $data['note_image'] ) ) $this->noteImage = $data['note_image'];
    if ( isset( $data['note_longitude'] ) ) $this->longitude = $data['note_longitude'];
    if ( isset( $data['note_latitude'] ) ) $this->latitude = $data['note_latitude'];
    if ( isset( $data['note_tags'] ) ) $this->noteTags = $data['note_tags'];
    if ( isset( $data['note_type'] ) ) $this->noteType = $data['note_type'];
    if ( isset( $data['temperature'] ) ) $this->temperature = $data['temperature'];
    if ( isset( $data['weatherIcon'] ) ) $this->weatherIcon = $data['weatherIcon'];
    if ( isset( $data['embed'] ) ) $this->embed = $data['embed'];
    if ( isset( $data['rsvp'] ) ) $this->rsvp = $data['rsvp'];
    if ( isset( $data['ping'] ) ) $this->ping = $data['ping'];
    if ( isset( $type ) ) $this->type = $data['type'];
  }

  /**
  * Sets the object's properties using the edit form post values in the supplied array
  *
  * @param assoc The form post values
  */

  public function storeFormValues ( $params, $type ) {

    // Store all the parameters
    $this->__construct( $params, $type );

    // Parse and store the publication date
    if ( isset($params['note_date']) ) {
      $noteDate = explode ( '-', $params['note_date'] );

      if ( count($noteDate) == 3 ) {
        list ( $y, $m, $d ) = $noteDate;
        $this->noteDate = mktime ( 0, 0, 0, $m, $d, $y );
      }
    }

    $this->type = $type;

  }

  /**
  * Returns an Note object matching the given Note ID
  *
  * @param int The Note ID
  * @return Note|false The Note object, or false if the record was not found or there was a problem
  */

  public static function getById( $id ) {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(note_date) AS note_date FROM notes WHERE id = :id";
    $st = $conn->prepare( $sql );
    $st->bindValue( ":id", $id, PDO::PARAM_INT );
    $st->execute();
    $row = $st->fetch();
    $conn = null;
    if ( $row ) return new Note( $row );
  }

  /**
  * Returns all posts (or a range of) Note objects in the DB
  *
  * @param int Optional The number of rows to return (default=all)
  * @param string Optional column by which to order the notes (default="note_date DESC")
  * @return Array|false A two-element array : results => array, a list of Note objects; totalRows => Total number of notes
  */

  public static function getList( $numRows=1000000, $order="note_date DESC", $note_type="note_type LIKE '%post%'" ) {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(note_date) AS note_date FROM notes WHERE " . $note_type . " AND note_type NOT LIKE '%removed%' ORDER BY " . $order . " LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $Note = new Note( $row );
      $list[] = $Note;
    }

    // Now get the total number of notes that matched the criteria
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
  }

  /**
  * Returns all posts matching contains
  *
  * @param int Optional The number of rows to return (default=all)
  * @param string Optional column by which to order the notes (default="note_date DESC")
  * @return Array|false A two-element array : results => array, a list of Note objects; totalRows => Total number of notes
  */

  public static function getListContains( $numRows=1000000, $order="note_date DESC", $like="note_tags LIKE '%post%'" ) {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(note_date) AS note_date FROM notes WHERE " . $like . " AND note_type NOT LIKE '%removed%' AND note_type NOT LIKE '%checkin%' ORDER BY " . $order . " LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $Note = new Note( $row );
      $list[] = $Note;
    }

    // Now get the total number of notes that matched the criteria
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
  }

   /**
  * Returns all posts matching date
  *
  * @param int Optional The number of rows to return (default=all)
  * @param string Optional column by which to order the notes (default="note_date DESC")
  * @return Array|false A two-element array : results => array, a list of Note objects; totalRows => Total number of notes
  */

  public static function getListByDate( $numRows=1000000, $order="note_date DESC", $like="note_tags LIKE '%post%'" ) {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(note_date) AS note_date FROM notes WHERE " . $like . " AND note_type NOT LIKE '%removed%' ORDER BY " . $order . " LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $Note = new Note( $row );
      $list[] = $Note;
    }

    // Now get the total number of notes that matched the criteria
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
  }

  /**
  * Returns all (or a range of) Note objects in the DB
  *
  * @param int Optional The number of rows to return (default=all)
  * @param string Optional column by which to order the notes (default="note_date DESC")
  * @return Array|false A two-element array : results => array, a list of Note objects; totalRows => Total number of notes
  */

  public static function getAll( $numRows=1000000, $order="note_date DESC", $note_type="note_type LIKE '%post%'" ) {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(note_date) AS note_date FROM notes WHERE note_type NOT LIKE '%removed%' AND note_type NOT LIKE '%checkin%' ORDER BY " . $order . " LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $Note = new Note( $row );
      $list[] = $Note;
    }

    // Now get the total number of notes that matched the criteria
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
  }

  /**
  * Inserts the current Note object into the database, and sets its ID property.
  */

  public function insert() {


    // Note text content from form
    $type = $this->type;
    $filename = '';
    $twOutput = 0;
    $noteContent = $this->noteText;

    // Location values
    if ( isset( $data['latitude'] ) ) :

      $noteLongitude = $data['longitude'];
      $noteLatitude = $data['latitude'];

    elseif ( isset($_POST["note_longitude"]) ) :

      $noteLongitude = $_POST["note_longitude"];
      $noteLatitude = $_POST["note_latitude"];

    elseif ( isset($_POST["location"]) ) :

      // Strip everything after comma
      $loc1 = strstr($_POST["location"],',',true);
      // Strip everything before comma
      $loc2 = strstr($_POST["location"],',');

      // Set replacements
      $replacements = array("," => "", ":" => "", "geo" => "");

      // Store final value
      $noteLongitude = strtr($loc2, $replacements);
      $noteLatitude = strtr($loc1, $replacements);

    else:

      $noteLongitude = '';
      $noteLatitude = '';

    endif;

    // Weather value
    if ( isset($_POST["temperature"]) ) :
    $temperature = $_POST["temperature"];
    $weatherIcon = $_POST["weatherIcon"];
    endif;

    // If image
    if ( isset($_FILES['note_image']['tmp_name']) ) :

        try {
            $img = new abeautifulsite\SimpleImage( $_FILES['note_image']['tmp_name'] );
            $img->best_fit(1400, 1400)->save( $_SERVER['DOCUMENT_ROOT']."/uploads/images/".$_FILES['note_image']['name'] );
            $img->best_fit(600, 600)->save( $_SERVER['DOCUMENT_ROOT']."/uploads/images/_small/".$_FILES['note_image']['name'] );
        } catch(Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

      // Set folder destination
      $filename = $_FILES['note_image']['name'];
      $filepath = $_SERVER['DOCUMENT_ROOT']."/uploads/images/".$_FILES['note_image']['name'];

    endif;

    //If posting to twitter
    if ( isset( $_POST["note_twitter"] )) :

      // Twitter
      $consumerKey = '{CONSUMER-KEY}';
      $consumerSecret = '{{CONSUMER-SECRET}}';
      $accessToken = '{{ACCESS-TOKEN}}';
      $accessTokenSecret = '{{ACCESS-TOKEN-SECRET}}';

      // Oauth
      $connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
      $content = $connection->get("account/verify_credentials");

      // If image
      if ( $_FILES['note_image']['tmp_name']!='' ) :
        // Set media path for Twitter
        $media1 = $connection->upload('media/upload', array('media' => $filepath));

        // If location present
        if ( isset( $_POST["note_longitude"] ) || isset( $_POST["location"] ) ) :
          $parameters = array(
            'status' => $noteContent,
            'lat' => $noteLatitude,
            'long' => $noteLongitude,
            'display_coordinates' => 'true',
            'geo_enabled' => 'true',
            'media_ids' => implode(',', array($media1->media_id_string))
          );
        else:
          $parameters = array(
            'status' => $noteContent,
            'media_ids' => implode(',', array($media1->media_id_string))
          );
        endif;

      else :
        $parameters = array(
          'status' => $noteContent
        );
      endif;

      // Post status
      $result = $connection->post('statuses/update', $parameters);

      // Receive post id
      $twOutput = $result->id_str;
    endif;

    // If photo upload
    if ( isset($_FILES['photo']) ) :

      $tmp_name = $_FILES["photo"]["tmp_name"];
      $name = basename($_FILES["photo"]["name"]);

      try {
        $img = new abeautifulsite\SimpleImage( $tmp_name );
        $img->best_fit(1400, 1400)->save( $_SERVER['DOCUMENT_ROOT']."/uploads/images/".$name );
        $img->best_fit(600, 600)->save( $_SERVER['DOCUMENT_ROOT']."/uploads/images/_small/".$name );
      } catch(Exception $e) {
        echo 'Error: ' . $e->getMessage();
      }

      $filename = $name;

    endif;

    // If Quill
    if ( isset($_POST['content']) ) :
      $noteContent = $_POST['content'];
    endif;

    // If Quill posts categories/tags
    if ( isset($_POST['category']) && is_array( $_POST['category'] ) ) :
      $noteTags = implode(', ', $_POST['category']);
    elseif ( isset($_POST['category']) && !is_array( $_POST['category'] ) ) :
      $noteTags = $_POST['category'];
    else:
      $noteTags = $this->noteTags;
    endif;


    /**
    * Copy an image from a remote URL to server
    */

    function image_save_from_url($my_img,$fullpath){
        if($fullpath!="" && $fullpath){
            $fullpath = $fullpath."/".basename($my_img);
        }
        $ch = curl_init($my_img);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0');
        $rawdata=curl_exec($ch);
        curl_close ($ch);
        if(file_exists($fullpath)){
            unlink($fullpath);
        }
        $fp = fopen($fullpath,'x');
        fwrite($fp, $rawdata);
        fclose($fp);
    }


    // If image url from Micropub / Ownyourgram / Ownyourswarm
    if ( isset($_POST['photo']) ) :

        $sourcePhoto    = $_POST['photo'];
        $filename       = basename($sourcePhoto);
        $destination    = $_SERVER['DOCUMENT_ROOT']."/uploads/images/_small/";

        image_save_from_url($sourcePhoto,$destination);

        $file_from_server = $destination+$filename;

    endif;

    // Set note type
    if ( isset( $_POST['note_type'] ) ) :

      $post_type = $_POST['note_type'];

    else:

      $post_type = 'post';

    endif;

    function getWeatherIcon( $latitude, $longitude ) {
      $key = '{{DARKSKY-API-KEY}}';
      $json = file_get_contents("https://api.darksky.net/forecast/$key/$latitude,$longitude");
      $obj = json_decode($json,true);

      return $obj['currently']['icon'];

    }

    function getTemperature( $latitude, $longitude ) {
      $key = '{{DARKSKY-API-KEY}}';
      $json = file_get_contents("https://api.darksky.net/forecast/$key/$latitude,$longitude");
      $obj = json_decode($json,true);

      $fahrenheit = $obj['currently']['temperature'];
      $celsius = round(($fahrenheit - 32)*5/9).'Â°C';

      return $celsius;

    }

    // Set note type if syndication URL set
    if( isset($_POST['syndication']) ) :

      // Convert to Syndacation URL object
      $url = parse_url($_POST["syndication"]);

      if ( $url['host'] == 'www.swarmapp.com' || $url['host'] == 'swarmapp.com' ) :
        $post_type  = 'checkin';

        // Run a weather API request
        $weatherIcon = getWeatherIcon($noteLatitude,$noteLongitude);
        $temperature = getTemperature($noteLatitude,$noteLongitude);

      endif;

    endif;

    // Does the Note object already have an ID?
    if ( !is_null( $this->id ) ) trigger_error ( "Note::insert(): Attempt to insert an Note object that already has its ID property set (to $this->id).", E_USER_ERROR );

    $date = date('Y-m-d G:i:s');

    if ( isset($_POST['published']) ) :
      $date = date('Y-m-d G:i:s', strtotime($_POST['published']));
    endif;

    // Insert the Note
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "INSERT INTO notes ( note_date, note_text, tweet_id, twitter, syndication, note_image, note_longitude, note_latitude, note_type, note_tags, temperature, weatherIcon, embed, rsvp, ping ) VALUES ( :note_date, :note_text, :tweet_id, :twitter, :syndication, :note_image, :note_longitude, :note_latitude, :note_type, :note_tags, :temperature, :weatherIcon, :embed, :rsvp, :ping );";
    $st = $conn->prepare ( $sql );
    $st->bindValue( ":note_date", $date, PDO::PARAM_INT );
    $st->bindValue( ":note_text", $noteContent, PDO::PARAM_STR );
    $st->bindValue( ":tweet_id", $twOutput, PDO::PARAM_STR );
    $st->bindValue( ":note_image", $filename, PDO::PARAM_STR );
    $st->bindValue( ":note_longitude", $noteLongitude, PDO::PARAM_STR );
    $st->bindValue( ":note_latitude", $noteLatitude, PDO::PARAM_STR );
    $st->bindValue( ":note_type", $post_type, PDO::PARAM_STR );
    $st->bindValue( ":note_tags", $noteTags, PDO::PARAM_STR );
    if ( isset($temperature) ) {
      $st->bindValue( ":temperature", $temperature, PDO::PARAM_STR );
      $st->bindValue( ":weatherIcon", $weatherIcon, PDO::PARAM_STR );
    } else {
      $st->bindValue( ":temperature", $this->temperature, PDO::PARAM_STR );
      $st->bindValue( ":weatherIcon", $this->weatherIcon, PDO::PARAM_STR );
    }
    $st->bindValue( ":embed", $this->embed, PDO::PARAM_STR );
    $st->bindValue( ":twitter", $this->twitter, PDO::PARAM_INT );
    $st->bindValue( ":syndication", $this->syndication, PDO::PARAM_STR );
    $st->bindValue( ":rsvp", $this->rsvp, PDO::PARAM_STR );
    $st->bindValue( ":ping", $this->ping, PDO::PARAM_STR );

    $st->execute();
    $this->id = $conn->lastInsertId();

    // If posting to Twitter
    if ( isset( $_POST["twitter"] )) :

      $endpoint = "https://brid.gy/publish/webmention";
      $source = "https://calumryan.com/note/".$this->id;
      $target = "https://brid.gy/publish/twitter";

      $client = new IndieWeb\MentionClient();
      $response = IndieWeb\MentionClient::sendWebmentionToEndpoint($endpoint, $source, $target, ['vouch'=>$vouch]);

    endif;

    if ( isset( $_POST["ping"] )) :

      $source = "https://calumryan.com/note/".$this->id;
      $target = $_POST["ping"];
      $client = new IndieWeb\MentionClient();
      $response = $client->sendWebmention($source, $target, ['vouch'=>$vouch]);

    endif;


    if ( $type == 'json' ) :

      $permalink = "https://calumryan.com/note/".$this->id;
      header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created');
      header('Location: ' . $permalink, true, 201);

    elseif ( isset( $_POST['content'] ) ) :

      $permalink = "https://calumryan.com/note/".$this->id;
      header($_SERVER['SERVER_PROTOCOL'] . ' 201 Created');
      header('Location: ' . $permalink, true, 201);

    endif;

    // Send POST request to Switchboard with curl
    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"https://switchboard.p3k.io/");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,"hub.mode=publish&hub.url=https://calumryan.com/");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $server_output = curl_exec($ch);
      curl_close ($ch);
    } catch (Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

    $conn = null;

  }


  /**
  * Updates the current Note object in the database.
  */

  public function update() {

    // Does the Note object have an ID?
    if ( is_null( $this->id ) ) trigger_error ( "Note::update(): Attempt to update an Note object that does not have its ID property set.", E_USER_ERROR );

    // Update the Note
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "UPDATE notes SET note_text=:note_text, note_latitude=:note_latitude, note_longitude=:note_longitude, note_type=:note_type, note_tags=:note_tags, temperature=:temperature, weatherIcon=:weatherIcon, embed=:embed, rsvp=:rsvp, syndication=:syndication, ping=:ping WHERE id = :id";
    $st = $conn->prepare ( $sql );
    $st->bindValue( ":note_text", $this->noteText, PDO::PARAM_STR );
    $st->bindValue( ":note_longitude", $this->longitude, PDO::PARAM_STR );
    $st->bindValue( ":note_latitude", $this->latitude, PDO::PARAM_STR );
    $st->bindValue( ":note_type", $this->noteType, PDO::PARAM_STR );
    $st->bindValue( ":note_tags", $this->noteTags, PDO::PARAM_STR );
    $st->bindValue( ":weatherIcon", $this->weatherIcon, PDO::PARAM_STR );
    $st->bindValue( ":temperature", $this->temperature, PDO::PARAM_STR );
    $st->bindValue( ":embed", $this->embed, PDO::PARAM_STR );
    $st->bindValue( ":syndication", $this->syndication, PDO::PARAM_STR );
    $st->bindValue( ":rsvp", $this->rsvp, PDO::PARAM_STR );
    $st->bindValue( ":ping", $this->ping, PDO::PARAM_STR );
    $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
    $st->execute();

    if ( isset( $this->ping )) :

      $source = "https://calumryan.com/note/".$this->id;
      $target = $this->ping;
      $client = new IndieWeb\MentionClient();
      $response = $client->sendWebmention($source, $target, ['vouch'=>$vouch]);

    endif;

    $conn = null;

  }


  /**
  * Deletes the current Note object from the database.
  */

  public function delete() {

    // Does the Note object have an ID?
    if ( is_null( $this->id ) ) trigger_error ( "Note::delete(): Attempt to delete an Note object that does not have its ID property set.", E_USER_ERROR );

    // Update the Note
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
    $sql = "UPDATE notes SET note_date=FROM_UNIXTIME(:note_date), note_text=:note_text, note_latitude=:note_latitude, note_longitude=:note_longitude, note_type=:note_type, note_tags=:note_tags, temperature=:temperature, weatherIcon=:weatherIcon, embed=:embed, rsvp=:rsvp, syndication=:syndication, ping=:ping WHERE id = :id";
    $st = $conn->prepare ( $sql );
    $st->bindValue( ":note_date", $this->noteDate, PDO::PARAM_INT );
    $st->bindValue( ":note_text", $this->noteText, PDO::PARAM_STR );
    $st->bindValue( ":note_longitude", $this->longitude, PDO::PARAM_STR );
    $st->bindValue( ":note_latitude", $this->latitude, PDO::PARAM_STR );
    $st->bindValue( ":note_type", "removed", PDO::PARAM_STR );
    $st->bindValue( ":note_tags", $this->noteTags, PDO::PARAM_STR );
    $st->bindValue( ":weatherIcon", $this->weatherIcon, PDO::PARAM_STR );
    $st->bindValue( ":temperature", $this->temperature, PDO::PARAM_STR );
    $st->bindValue( ":embed", $this->embed, PDO::PARAM_STR );
    $st->bindValue( ":syndication", $this->syndication, PDO::PARAM_STR );
    $st->bindValue( ":rsvp", $this->rsvp, PDO::PARAM_STR );
    $st->bindValue( ":ping", $this->ping, PDO::PARAM_STR );
    $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
    $st->execute();

    if ( isset( $this->ping )) :

      $source = "https://calumryan.com/note/".$this->id;
      $target = $this->ping;
      $client = new IndieWeb\MentionClient();
      $response = $client->sendWebmention($source, $target, ['vouch'=>$vouch]);

    endif;

    $conn = null;


  }

}

?>
