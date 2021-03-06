<?php
require_once ('Plans.php');
$mysearch = (isset($_GET['mysearch']) ? $_GET['mysearch'] : false);
$planlove = (isset($_GET['planlove']) ? $_GET['planlove'] : false);
$donotsearch = (isset($_GET['donotsearch']) ? $_GET['donotsearch'] : false);
require ('functions-main.php');
require ('syntax-classes.php'); //load display functions
$dbh = db_connect(); //connect to the database
$idcookie = User::id();
$context = 100; //set the number of characters around found item
$thispage = new PlansPage('Utilities', 'search', PLANSVNAME . ' - Search Plans', 'search.php');
if (User::logged_in()) {
    populate_page($thispage, $dbh, $idcookie);
} //begin user display
else {
    populate_guest_page($thispage);
} //otherwise begin guest display
$searchform = new Form('searchform', true);
$searchform->method = 'GET';
$thispage->append($searchform);
$searchprompt = new FormItemSet('search_opts', true);
$searchform->append($searchprompt);
$item = new TextInput('mysearch', $mysearch);
$searchprompt->append($item);
$item = new CheckboxInput('planlove', 1);
$item->description = 'Planlove';
$item->checked = $planlove;
$searchprompt->append($item);
$item = new SubmitInput('Search');
$searchform->append($item);
if ($mysearch) //if no search query, give search form
//otherwise perform the search
{
    if ($mysearch == "_") //if they just searched for an underscore
    {
        $err = AlertText('Invalid search term.');
        $thispage->append($err);
    } //tell them it's an invalid search
    else
    //otherwise, go on with the search
    {
        // Make the beginnings of our query
        $q = Doctrine_Query::create();
        if ($planlove) {
            $thesearchnum = get_item($dbh, "userid", "accounts", "username", $mysearch);
            if (!$thesearchnum) {
                $err = new AlertText("Plan [$mysearch] does not exist. Please check your spelling.", 'Search failed');
                $thispage->append($err);
                $donotsearch = true;
            } else {
                $plansearchname = "[" . $mysearch . "]";
                $mysearch = $plansearchname;
                $donotsearch = false;
            }
        } //if planlove
        if (!$donotsearch) {
            $mysearch = preg_replace("/\&/", "&amp;", $mysearch);
            $mysearch = preg_replace("/\</", "&lt;", $mysearch);
            $mysearch = preg_replace("/\>/", "&gt;", $mysearch);
            $mysearch = preg_quote($mysearch);
            $ids_to_hide = Block::allUserIdsWithBlockingRelationships($idcookie);
            if ($planlove) {
                // hit the index
                $q->select('a.username, p.plan, l.lover_id');
                $q->from('Planlove l')->leftJoin('l.Lover a')->leftJoin('a.Plan p');
                $q->where('l.lovee_id = ?', "$thesearchnum");
                $q->andWhereNotIn('l.lover_id', $ids_to_hide);
            } else {
                // do a slow query
                $q->select('a.username, p.plan');
                $q->from('Accounts a')->leftJoin('a.Plan p');
                $q->where('p.edit_text LIKE ?', "%$mysearch%");
                $q->andWhereNotIn('a.userid', $ids_to_hide);
            };
            if (!$idcookie) {
                $q->addWhere('a.webview=1');
            }
            $q->orderBy('a.username');
            // Run the query
            $results = $q->execute();
            $result_list = new WidgetList('search_results', true);
            $thispage->append($result_list);
            foreach($results as $row) {
                // Just stuff our results into an array for the legacy code
                if ($planlove) {
                    $new_row = array(0 => $row->Lover->username, 1 => $row->Lover->Plan->plan,);
                } else {
                    $new_row = array(0 => $row->username, 1 => $row->Plan->plan,);
                };
                //$new_row[1] is the plan content

                //strip all tags except '<br>' and '<br/>' 
                $new_row[1] = strip_tags($new_row[1], '<br>');

                $new_row[1] = stripslashes($new_row[1]);
                if ($planlove) {
                    $mysearch = $plansearchname;
                }
                //$mysearch = preg_quote($mysearch,"/");
                /*
                */
                $matchcount = preg_match_all("/(" . preg_quote($mysearch, "/") . ")/si", $new_row[1], $matcharray);
                $new_row[1] = preg_replace("/(" . preg_quote($mysearch, "/") . ")/si", "<b>\\1</b>", $new_row[1]);
                /*
                $matchcount = preg_match_all("/(" . $mysearch . ")/si", $new_row[1], $matcharray);
                $new_row[1] = preg_replace("/(" . $mysearch . ")/si", "<b>\\1</b>", $new_row[1]);
                */
                $result = new WidgetGroup('result_user_group', false);
                $result_list->append($result);
                $name = new PlanLink($new_row[0]);
                $result->append($name);
                $count = new RegularText($matchcount);
                $result->append($count);
                $start_array = array();
                $end_array = array();
                $o = 0;
                $pos = strpos($new_row[1], "<b>"); //find where matched term starts
                while ($o < $matchcount) {
                    array_push($start_array, $pos - $context);
                    $pos = strpos($new_row[1], "</b>", $pos) + 4;
                    array_push($end_array, $pos + $context);
                    $pos = strpos($new_row[1], "<b>", $pos); //find where matched term starts
                    $o++;
                } //While $o<$matchnout-1
                $num = 0;
                while ($num < count($start_array) - 1) {
                    if ($end_array[$num] >= $start_array[$num + 1]) {
                        $end_array[$num] = $end_array[$num + 1];
                        array_splice($start_array, $num + 1, 1);
                        array_splice($end_array, $num + 1, 1);
                    } else {
                        $num++;
                    }
                } //while $o<$matchcount-1
                $sublist = new WidgetList('result_sublist', false);
                $result->append($sublist);
                $endsize = strlen($new_row[1]);
                for ($num = 0;$num < count($start_array);$num++) {
                    //Produce excerpts
                    if ($start_array[$num] < 0) {
                        $start_array[$num] = 0;
                    }
                    if ($end_array[$num] > $endsize) {
                        $end_array[$num] = $endsize;
                    }
                    //Try to start our excerpt on a space.
                    $startof = strpos($new_row[1], " ", $start_array[$num]);
                    if ($startof === FALSE) {
                        $startof = $start_array[$num]; // If not possible to start context
                        //	between words, then don't worry about it
                        
                    }
                    //but don't look past our search match!
                    if ($startof > strpos($new_row[1], "<b>", $start_array[$num])) {
                        $startof = strpos($new_row[1], "<b>", $start_array[$num]);
                    }
                    //Try to end the excerpt on a space, but don't look too far.
                    //This used to quote entire plans, if they didn't have any
                    //spaces (huge planlove lists, for instance).
                    $endof = strpos($new_row[1], " ", $end_array[$num]);
                    if ($endof === false or $endof > $end_array[$num] + 20) {
                        $endof = $end_array[$num];
                        // Check for truncated <br> tags that screw stuff up
                        if ((substr($new_row[1], $endof - 2, 3) == "<br" && $i = 3) || (substr($new_row[1], $endof - 1, 2) == "<b" && $i = 2) || (substr($new_row[1], $endof, 1) == "<" && $i = 1)) {
                            $endof-= $i;
                        }
                    }
                    //Don't try to read past the end of the plan.
                    $endof = min($endof, $endsize);
                    $textitem = new RegularText(substr($new_row[1], $startof, $endof - $startof), false);
                    $sublist->append($textitem);
                } //while still displaying parts of plan
                
            } //while dealing with one plan that has term
            if (!($matchcount > 0)) {
                $ohwell = new InfoText('No matches found.');
                $thispage->append($ohwell);
            }
        } //if is not marked as do not search
        
    } //if search is not an underscore
    
} //if something is entered to search for
interface_disp_page($thispage);
db_disconnect($dbh);
?>
