<?php

// Get displayed user id first
$userID = bbp_get_displayed_user_id();
$user   = get_userdata( $userID );
$first_name = get_user_meta($userID,"first_name");
$last_name  = get_user_meta($userID,"last_name");
$user->real_name  = sprintf("%s %s",$first_name[0],$last_name[0]);


// ------------------------------------------------------------------
// Helper function to display the table rows
// ------------------------------------------------------------------
function show_row($key,$value) {
   echo"   <tr>\n"
      ."      <td class='key'>".$key.":</td>\n"
      ."      <td>".$value."</td>\n"
      ."   </tr>\n";

}

// ------------------------------------------------------------------
// Creating city statistics
// ------------------------------------------------------------------
function get_city_stats( $cityID, $userID ) {

   global $WTuser, $wpdb;
   // Do not show results for today - number of bet days - 1
   // To show only fully finished tournaments in this 'stats'
   $tdatebefore    = (int)$WTuser->options->wetterturnier_betdays;
   $tdatebefore    = (int)(time()/86400) - $tdatebefore - 1;

   $sql  = "SELECT min(tdate) AS min, max(tdate) AS max, count(tdate) AS count";
   $sql .= sprintf(" FROM %swetterturnier_betstat",$wpdb->prefix);
   $sql .= sprintf(" WHERE userID = %d AND cityID = %d",$userID,$cityID);
   $sql .= sprintf(" AND tdate < %d",$tdatebefore);
   $res = $wpdb->get_row($sql);
   if ( ! $res ) { return(__("No information available","wpwt")); }
   // Converting date
   $first = $WTuser->date_format($res->min);
   $last  = $WTuser->date_format($res->max);

   // Checking if on first three ranks
   $sql = array();
   array_push($sql,sprintf("SELECT rank, count(rank) AS count"));
   array_push($sql,sprintf("FROM %swetterturnier_betstat",$wpdb->prefix));
   array_push($sql,sprintf("WHERE cityID = %d AND userID = %d AND rank <= 3",$cityID,$userID));
   array_push($sql,sprintf(" AND tdate < %d",$tdatebefore));
   array_push($sql,sprintf("GROUP BY rank ORDER BY rank"));
   //$rank_res = $wpdb->get_results(join("\n",$sql));
   $rankhistory = new stdClass();
   $rankhistory->rank_1 = 0;
   $rankhistory->rank_2 = 0;
   $rankhistory->rank_3 = 0;
   foreach ( $wpdb->get_results(join("\n",$sql)) as $rec ) {
      $tmp = sprintf("rank_%d",$rec->rank);
      $rankhistory->$tmp = $rec->count;
   }
   // Return string
   if ( $res->count > 0 ) {
      $return = array();
      array_push($return,sprintf("<span class='rankhistory first'>%d</span>",$rankhistory->rank_1));
      array_push($return,sprintf("<span class='rankhistory second'>%d</span>",$rankhistory->rank_2));
      array_push($return,sprintf("<span class='rankhistory third'>%d</span>",$rankhistory->rank_3));
      array_push($return,"<br>");
      array_push($return, sprintf("In total <b>%d</b> %s %s %s %s %s",
                          $res->count,__("participations","wpwt"),__("between","wpwt"),
                          $first, __("and","wpwt"), $last) );

      return( join("\n",$return) );

   } else {
      return( __("<span style='color: gray;'>Never participated</span>","wpwt") );
   }
}
?>

<style type="text/css">
div#bbp-user-profile div.bbp-user-section { display: none; }
table#wt-profile-table, table#wt-profile-table tr, table#wt-profile-table tr td {
   border: none;
}
table#wt-profile-table tr td.key {
   font-weight: bold;
   font-size: 0.8em;
   text-transform: uppercase;
   width: 200px;
}
</style>

<table id='wt-profile-table'>
<?php
// Globalize class
global $WTuser;

// Some user infos
show_row(__("Username","wpwt"),         $user->display_name);
show_row(__("Name","wpwt"),             $user->real_name);
show_row(__("Registered since","wpwt"), $user->user_registered);

// Loading user bio for the current language
$user_lang = $WTuser->get_user_language("slug");
// Try to load user description based on user language
$bio = get_user_meta($userID,sprintf("sescription_%s",$user_lang),true);
if ( strlen($bio) == 0 ) {
   $bio = get_user_meta($userID,"description",true);
}
if ( strlen($bio) > 0 ) { show_row(sprintf("%s:",__("Biography","wpwt")),
                          sprintf("<b>%s</b>",$bio)); }

// User roles
$roles = array();
foreach ( $user->wp_capabilities as $key=>$val ) { if ( $val ) { array_push($roles,$key); } }
show_row(__("capabilities","wpwt"),join(", ",$roles));


// Show statistics for each city
$cities = $WTuser->get_all_cityObj();
foreach ( $cities as $cityObj ) {
   show_row(sprintf("%s %s",__("participations","wpwt"),
            $cityObj->get('name')),get_city_stats($cityObj->get('ID'),$userID));
}
?>
</table>
