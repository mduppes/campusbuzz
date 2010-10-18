<?php

require_once realpath(LIB_DIR.'/Module.php');
require_once realpath(LIB_DIR.'/TimeRange.php');
require_once realpath(LIB_DIR.'/ICalendar.php');

define('DAY_SECONDS', 24*60*60);

class CalendarModule extends Module {
  protected $id = 'calendar';
  protected $timezone;

  private $controllerClass = '';
  private $parserClass     = '';
  private $eventClass      = '';
  private $baseURL         = '';

  private $searchOptions = array(
    array("phrase" => "in the next 7 days",   "offset" => 7),
    array("phrase" => "in the next 15 days",  "offset" => 15),
    array("phrase" => "in the next 30 days",  "offset" => 30),
    array("phrase" => "in the past 15 days",  "offset" => -15),
    array("phrase" => "in the past 30 days",  "offset" => -30),
    //array("phrase" => "this school term",     "offset" => "term"),
    //array("phrase" => "this school year",     "offset" => "year")
  );
  
  private function getDatesForSearchOption($option) {
    $start = $end = time();
    
    switch ($option['offset']) {
      case 'term':
        // TODO
        break;
        
      case 'year':
        // TODO
        break;
        
      default: // day counts TODO: This is not daylight saving time safe
        if ($option['offset'] >= 0) {
          $end = $start + ($option['offset']*DAY_SECONDS);
        } else {
          $start = $end + ($option['offset']*DAY_SECONDS);
        }
        break;
    }

    return array (
      new DateTime(date('Y-m-d H:i:s', $start), $this->timezone), 
      new DateTime(date('Y-m-d H:i:s', $end  ), $this->timezone),
    );
  }


  private function dayInfo($time, $offset=0) {
    $time += $offset * DAY_SECONDS;
    return array(
      "weekday"       => date('l', $time),
      "month"         => date('F', $time),
      "month_3Let"    => date('M', $time),
      "day_num"       => date('j', $time),
      "year"          => date('Y', $time),
      "month_num"     => date('m', $time),
      "day_3Let"      => date('D', $time),
      "day_num_2dig"  => date('d', $time),
      "date"          => date('Y/m/d', $time),
      "time"          => strtotime(date("Y-m-d 12:00:00", $time))
    );
  }
  
  private function timeText($event) {
    return strval($event->get_range());
    if ($event->get_end() - $event->get_start() == -1) {
      return $event->get_range()->format('D M j').' '.date('g:i a', $event->get_start());
    }
    return $event->get_range()->format('D M j g:i a');
  }

  private function ucname($name) {
    $new_words = array();
    foreach(explode(' ', $name) as $word) {
      $new_word = array();
      foreach(explode('/', $word) as $sub_word) {
        $new_word[] = ucwords($sub_word);
      }
      $new_word = implode('/', $new_word);
      $new_words[] = $new_word;
    } 
    return implode(' ', $new_words);
  }

  private function searchDates($option) {
    $offset = $this->options[$option]["offset"];
    $time = time();
    $day1 = dayInfo($time);

    if(is_int($offset)) {
      $day2 = dayInfo($time, $offset);
      if($offset > 0) {
        return array("start" => $day1['date'], "end" => $day2['date']);
      } else {
        return array("start" => $day2['date'], "end" => $day1['date']); 
      }
    } else {
      switch($offset) {
        case "term":
          if($day1['month_num'] < 7) {
            $endDate = "{$day1['year']}/07/01";
      } else {
            $endDate = "{$day1['year']}/12/31";
          }
          break;

        case "year": 
          if($day1['month_num'] < 7) {
            $endDate = "{$day1['year']}/07/01";
      } else {
            $year = $day1['year'] + 1;
            $endDate = "$year/07/01";
          }
          break;
      }    
      return array("start" => $day1['date'], "end" => $endDate); 
    }
  }
  
  private function valueForType($type, $value) {
    $valueForType = $value;
  
    switch ($type) {
      case 'datetime':
        if (is_a($value, 'DayRange')) {
          $valueForType = strval($value);
        } else {
          $valueForType = date("D M j", $value->get_start());
          if ($value->get_end() && $value->get_end()!=$value->get_start()) {
            if (date('Ymd', $value->get_start()) != date('Ymd', $value->get_end())) {
              $valueForType .= date(' g:i', $value->get_start());
              if (date('a', $value->get_start()) != date('a', $value->get_end())) {
                $valueForType .= date(' a', $value->get_start());
              }
        
              $valueForType .= date(" - D M j g:i a", $value->get_end());
            } else {
              $valueForType .= "<br/>" . date('g:i', $value->get_start()) . date("-g:i a", $value->get_end());
            }
          } else {
            $valueForType .= "<br/>" . date('g:i a', $value->get_start());
          }
        }
        
        break;

      case 'url':
        $valueForType = str_replace("http://http://", "http://", $value);
        if (strlen($valueForType) && !preg_match('/^http\:\/\//', $valueForType)) {
          $valueForType = 'http://'.$valueForType;
        }
        break;
        
      case 'phone':
        // add the local area code if missing
        if (preg_match('/^\d{3}-\d{4}/', $value)) {
          $valueForType = $GLOBALS['siteConfig']->getVar('LOCAL_AREA_CODE').$value;
        }
        $valueForType = str_replace('-', '-&shy;', str_replace('.', '-', $value));
        break;
      
      case 'email':
        $valueForType = str_replace('@', '@&shy;', $value);
        break;
        
      case 'category':
        $valueForType = $this->ucname($value);
        break;
    }
    
    return $valueForType;
  }
  
  private function urlForType($type, $value) {
    $urlForType = null;
  
    switch ($type) {
      case 'url':
        $urlForType = str_replace("http://http://", "http://", $value);
        if (strlen($urlForType) && !preg_match('/^http\:\/\//', $urlForType)) {
          $urlForType = 'http://'.$urlForType;
        }
        break;
        
      case 'phone':
        // add the local area code if missing
        if (preg_match('/^\d{3}-\d{4}/', $value)) {
          $urlForType = $GLOBALS['siteConfig']->getVar('LOCAL_AREA_CODE').$value;
        }
    
        // remove all non-word characters from the number
        $urlForType = 'tel:1'.preg_replace('/\W/', '', $value);
        break;
        
      case 'email':
        $urlForType = "mailto:$value";
        break;
        
      case 'category':
        $urlForType = $this->categoryURL($value, false);
        break;
    }
    
    return $urlForType;
  }

  // URL DEFINITIONS
  private function dayURL($day, $type, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('day', array(
      'time' => $day['time'],
      'type' => $type,
    ), $addBreadcrumb);
  }
  
  private function categoryDayURL($day, $categoryID, $name, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('category', array(
      'time' => $day['time'],
      'id'   => $categoryID,
      'name' => $name, 
    ), $addBreadcrumb);
  }
  
  private function academicURL($year, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('academic', array(
      'year' => $year,
    ), $addBreadcrumb);
  }
  
  private function holidaysURL($year=NULL, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('holidays', array(
      'year' => isset($year) ? $year : $this->args['year'],
    ), $addBreadcrumb);
  }
  
  private function religiousURL($year=NULL, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('holidays', array(
      'page' => 'religious',
      'year' => isset($year) ? $year : $this->args['year'],
    ), $addBreadcrumb);
  }
  
  private function categoriesURL($addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('categories', array(), $addBreadcrumb);
  }
  
  private function categoryURL($category, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('category', array(
      'id'   => is_array($category) ? $category['catid'] : $category->get_cat_id(),
      'name' => is_array($category) ? $category['name']  : $this->ucname($category->get_name()),
    ), $addBreadcrumb);
  }
  
  private function subCategorysURL($category, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('sub-categorys', array(
      'id' => is_array($category) ? $category['catid'] : $category->get_cat_id(),
    ), $addBreadcrumb);
  }
  
  private function detailURL($event, $addBreadcrumb=true) {
    return $this->buildBreadcrumbURL('detail', array(
      'id'   => $event->get_uid(),
      'time' => $event->get_start()
    ), $addBreadcrumb);
  }
  
  public function federatedSearch($searchTerms, $maxCount, &$results) {
    $searchOption = $this->searchOptions[0]; // default timeframe
    
    $feed = new $this->controllerClass($this->baseURL, new $this->parserClass);
    $feed->setObjectClass('event', $this->eventClass);
    
    list($start, $end) = $this->getDatesForSearchOption($searchOption);          
    $feed->setStartDate($start);
    $feed->setEndDate($end);
    $feed->addFilter('search', $searchTerms);
    $iCalEvents = array_values($feed->items());

    $limit = min($maxCount, count($iCalEvents));
    for ($i = 0; $i < $limit; $i++) {
      $subtitle = $this->timeText($iCalEvents[$i]);
      $briefLocation = $iCalEvents[$i]->get_location();
      if (isset($briefLocation)) {
        $subtitle .= " | $briefLocation";
      }
  
      $results[] = array(
        'url'      => $this->buildBreadcrumbURL("/{$this->id}/detail", array(
            'id'   => $iCalEvents[$i]->get_uid(),
            'time' => $iCalEvents[$i]->get_start()
          ), false),
        'title'    => $iCalEvents[$i]->get_summary(),
        'subtitle' => $subtitle,
      );
    }
    
    return count($iCalEvents);
  }
  
  protected function urlForSearch($searchTerms) {
    return $this->buildBreadcrumbURL("/{$this->id}/search", array(
      'filter'    => $searchTerms,
      'timeframe' => '0',
    ), false);
  }


  protected function initialize() {
    $this->controllerClass = $GLOBALS['siteConfig']->getVar('CALENDAR_CONTROLLER_CLASS');
    $this->parserClass     = $GLOBALS['siteConfig']->getVar('CALENDAR_PARSER_CLASS');
    $this->eventClass      = $GLOBALS['siteConfig']->getVar('CALENDAR_EVENT_CLASS');
    $this->baseURL         = $GLOBALS['siteConfig']->getVar('CALENDAR_ICS_URL');
    
    $this->timezone = new DateTimeZone($GLOBALS['siteConfig']->getVar('LOCAL_TIMEZONE'));
  }

  protected function initializeForPage() {
    switch ($this->page) {
      case 'help':
        break;
        
      case 'index':
        $today = $this->dayInfo(time());
      
        $this->assign('today',           $today);
        $this->assign('searchOptions',   $this->searchOptions);
        
        $this->assign('todaysEventsUrl', $this->dayURL($today, 'events'));
        $this->assign('holidaysUrl',     $this->holidaysURL($today['year']));
        $this->assign('categoriesUrl',    $this->categoriesURL());
        $this->assign('academicUrl',     $this->academicURL($today['year']));

        break;
      
      case 'categories':
        $categories = array();
        
        $categoryObjects = call_user_func(array($this->eventClass, 'get_all_categories'));

        foreach ($categoryObjects as $categoryObject) {
          $categories[] = array(
            'title' => $this->ucname($categoryObject->get_name()),
            'url' => $this->categoryURL($categoryObject),
          );
        }
        
        $this->assign('categories', $categories);
        break;
      
      case 'category':
        $id   = $this->getArg('id', '');
        $name = $this->getArg('name', '');
        $time = $this->getArg('time', time());

        $this->setBreadcrumbTitle($name);
        $this->setBreadcrumbLongTitle($name);

        $this->assign('category', $this->ucname($name));
        
        $dayRange = new DayRange(time());
        $next = $this->dayInfo($time, 1);
        $prev = $this->dayInfo($time, -1);
        
        $this->assign('current', $this->dayInfo($time));
        $this->assign('next',    $next);
        $this->assign('prev',    $prev);
        $this->assign('nextUrl', $this->categoryDayURL($next, $id, $name, false));
        $this->assign('prevUrl', $this->categoryDayURL($prev, $id, $name, false));
        $this->assign('isToday', $dayRange->contains(new TimeRange($time)));

        $events = array();
        
        if (strlen($id) > 0) {
            $feed = new $this->controllerClass($this->baseURL, new $this->parserClass);
            $feed->setObjectClass('event', $this->eventClass);
            
            $start = new DateTime(date('Y-m-d H:i:s', $time), $this->timezone);
            $start->setTime(0,0,0);
            $end = clone $start;
            $end->setTime(23,59,59);
    
            $feed->setStartDate($start);
            $feed->setEndDate($end);
            $feed->addFilter('category', $id);
            $iCalEvents = $feed->items();
          
          foreach($iCalEvents as $iCalEvent) {
            $subtitle = $this->timeText($iCalEvent);
            $briefLocation = $iCalEvent->get_location();
            if (isset($briefLocation)) {
              $subtitle .= " | $briefLocation";
            }
          
            $events[] = array(
              'url'      => $this->detailURL($iCalEvent),
              'title'    => $iCalEvent->get_summary(),
              'subtitle' => $subtitle,
            );
          }
        }
        
        $this->assign('events', $events);        
        break;
      
      case 'day':  
        $type = isset($this->args['type']) ? $this->args['type'] : 'events';
        $this->assign('Type', ucwords($type));

        $time = isset($this->args['time']) ? $this->args['time'] : time();
        $next = $this->dayInfo($time, 1);
        $prev = $this->dayInfo($time, -1);
        $this->assign('current', $this->dayInfo($time));
        $this->assign('next',    $next);
        $this->assign('prev',    $prev);
        $this->assign('nextUrl', $this->dayURL($next, $type, false));
        $this->assign('prevUrl', $this->dayURL($prev, $type, false));
        
        $feed = new $this->controllerClass($this->baseURL, new $this->parserClass);
        $feed->setObjectClass('event', $this->eventClass);
        
        $start = new DateTime(date('Y-m-d H:i:s', $time), $this->timezone);
        $start->setTime(0,0,0);
        $end = clone $start;
        $end->setTime(23,59,59);

        $feed->setStartDate($start);
        $feed->setEndDate($end);
        $iCalEvents = $feed->items();
                
        $events = array();
        foreach($iCalEvents as $iCalEvent) {
          $subtitle = $this->timeText($iCalEvent);
          $briefLocation = $iCalEvent->get_location();
          if (isset($briefLocation)) {
            $subtitle .= " | $briefLocation";
          }
        
          $events[] = array(
            'url'      => $this->detailURL($iCalEvent),
            'title'    => $iCalEvent->get_summary(),
            'subtitle' => $subtitle
          );
        }
        $this->assign('events', $events);        
        break;
        
      case 'detail':  
        $calendarFields = $this->loadThemeConfigFile('calendar-detail', 'detailFields');

        $feed = new $this->controllerClass($this->baseURL, new $this->parserClass);
        $feed->setObjectClass('event', $this->eventClass);
        
        $time = isset($this->args['time']) ? $this->args['time'] : time();
        if ($event = $feed->getItem($this->args['id'], $time)) {
          $this->assign('event', $event);
        } else {
          throw new Exception("Event not found");
        }
            
        // build the list of attributes
        $allKeys = array_keys($calendarFields);

        $fields = array();
        foreach ($calendarFields as $key => $info) {
          if ($key == '*') {
            $skipKeys = $allKeys;
            if (isset($info['suppress'])) {
              $skipKeys = array_merge($skipKeys, $info['suppress']);
            }
            $extraKeys = array_diff($event->get_all_attributes(), $skipKeys);
            
            foreach ($extraKeys as $key) {
              if ($key != '*' && substr_compare($key, 'X-', 0, 2, true) != 0) {
                $fields[$key] = array(
                  'label' => $this->ucname($key),
                  'title' => $event->get_attribute($key),
                );  
              }
            }
            
          } else {
            $field = array();
            
            $value = $event->get_attribute($key);
            if (!isset($value)) { continue; }

            if (isset($info['label'])) {
              $field['label'] = $info['label'];
            }
            
            if (isset($info['class'])) {
              $field['class'] = $info['class'];
            }
            
            if (is_array($value)) {		
              $fieldValues = array();
              foreach ($value as $item) {
                $fieldValue = '';
                $fieldValueUrl = null;
                
                if (isset($info['type'])) {
                  $fieldValue  = $this->valueForType($info['type'], $item);
                  $fieldValueUrl = $this->urlForType($info['type'], $item);
                } else {
                  $fieldValue = $item;
                }
                
                if (isset($fieldValueUrl)) {
                  $fieldValue = '<a href="'.$fieldValueUrl.'">'.$fieldValue.'</a>';
                }
                
                $fieldValues[] = $fieldValue;
              }
              $field['title'] = implode(', ', $fieldValues);
            
            } else {
              if (isset($info['type'])) {
                $field['title'] = $this->valueForType($info['type'], $value);
                $field['url']   = $this->urlForType($info['type'], $value);
              } else {
                $field['title'] = nl2br($value);
              }
            }
            
            $fields[] = $field;
          }
        }
    
        $this->assign('fields', $fields);
        //error_log(print_r($fields, true));
        break;
        
      case 'search':
        if (isset($this->args['filter'], $this->args['timeframe'])) {
          $searchTerms = trim($this->args['filter']);
          $timeframeKey = $this->args['timeframe'];
          $searchOption = $this->searchOptions[$timeframeKey];
          
          $feed = new $this->controllerClass($this->baseURL, new $this->parserClass);
          $feed->setObjectClass('event', $this->eventClass);
          
          list($start, $end) = $this->getDatesForSearchOption($searchOption);          
          $feed->setStartDate($start);
          $feed->setEndDate($end);
          $feed->addFilter('search', $searchTerms);
          $iCalEvents = $feed->items();

          $events = array();
          foreach($iCalEvents as $iCalEvent) {
            $subtitle = $this->timeText($iCalEvent);
            $briefLocation = $iCalEvent->get_location();
            if (isset($briefLocation)) {
              $subtitle .= " | $briefLocation";
            }
        
            $events[] = array(
              'url'      => $this->detailURL($iCalEvent),
              'title'    => $iCalEvent->get_summary(),
              'subtitle' => $subtitle
            );
          }
                    
          $this->assign('events',      $events);        
          $this->assign('searchTerms', $searchTerms);        

        } else {
          $this->redirectTo('index');
        }
        break;
        
      case 'academic':
        $baseURL = $GLOBALS['siteConfig']->getVar('CALENDAR_ACADEMIC_ICS_URL');
        $year = isset($this->args['year']) ? intval($this->args['year']) : date('Y');

        $start = new DateTime( $year   ."0901", $this->timezone);        
        $end   = new DateTime(($year+1)."0831", $this->timezone);
        
        $feed = new $this->controllerClass($baseURL, new $this->parserClass);
        $feed->setObjectClass('event', $this->eventClass);
        $feed->setStartDate($start);
        $feed->setEndDate($end);
        $iCalEvents = $feed->items();

        $events = array();
        foreach($iCalEvents as $iCalEvent) {
          $events[] = array(
            'title'    => $iCalEvent->get_summary(),
            'subtitle' => date('l F j', $iCalEvent->get_start()),
          );
        }

        $current =  $year   .'-'.($year+1);
        $next    = ($year+1).'-'.($year+2);
        $prev    = ($year-1).'-'. $year;

        if ((date('Y')+1) > $year) {
          $this->assign('next',    $next);
          $this->assign('nextUrl', $this->academicURL($year+1, false));
        }
        if ($year > intval(date('Y'))) {
          $this->assign('prev',    $prev);
          $this->assign('prevUrl', $this->academicURL($year-1, false));
        }

        $this->assign('current', $current);
        $this->assign('events',  $events);        
        break;
    }
    
  }
}
