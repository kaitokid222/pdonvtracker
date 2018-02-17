<?php
class Holidays
{
    // Array mit allen Feiertagen
    private $_holidays = array();

    /**
     * Klasse erzeugen
     *
     * @access   public
     */
    public function __construct(){
        $this -> _setHolidays();
    }

    /**
     * Klasse freigeben
     *
     * @access   public
     */
    public function __destruct(){
        unset($this -> _holidays);
    }

    /**
     * Überprüft, ob ein Datum ein Feiertag ist
     *
     * @param    datetime/string     Datums-Objekt oder Englischer Datums-String [Y-m-d h:i:s]
     * @return   bool
     * @access   public
     */
    public function isDateIsHoliday($date = 'now')
    {
        if ( is_string($date) )
        {
            $date = new DateTime($date);
        }

        $date -> setTime(0, 0, 0);

        foreach( $this -> _holidays AS $holy => $day )
        {
            if ( $date == $day['date'] )
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Gibt das Datum zu einem Feiertag zurück
     *
     * @param    string         Name des Feiertages
     * @return   string
     * @access   public
     */
    public function getDateFromHoliday($holyName = '', $germanFormat = TRUE)
    {
        $holyName = $this -> _cleanHolyName($holyName);

        if ( empty($holyName) OR !strlen($holyName) OR !array_key_exists($holyName, $this -> _holidays) )
        {
            return FALSE;
        }
        else
        {
            if ( $germanFormat )
            {
                return $this -> _holidays[$holyName]['date'] -> format('d.m.Y');
            }
            else
            {
                return $this -> _holidays[$holyName]['date'] -> format('Y-m-d');
            }
        }
    }

    /**
     * Gibt den Namen zu einem Feiertag zurück
     *
     * @param    string/date         Name des Feiertages oder DateTime-Objekt
     * @return   string/bool
     * @access   public
     */
    public function getNameFromHoliday($holyName = null)
    {
        if ( is_null($holyName) )
        {
            return FALSE;
        }

        if ( $holyName instanceof DateTime )
        {
            foreach( $this -> _holidays AS $holy => $date )
            {
                if ( $holyName == $date['date'] )
                {
                    return $date['name'];
                }
            }

            return FALSE;
        }

        if ( is_string($holyName) )
        {
            $holyName = $this -> _cleanHolyName($holyName);

            if ( empty($holyName) OR !strlen($holyName) OR !array_key_exists($holyName, $this -> _holidays) )
            {
                return FALSE;
            }
            else
            {
                return $this -> _holidays[$holyName]['name'];
            }
        }
    }

    /**
     * Alle Feiertage anzeigen
     *
     * @return   array
     * @access   public
     */
    public function getAllHolidays()
    {
        return $this -> _holidays;
    }


    /**
     * Umlaute und Leerzeichen entfernen
     *
     * @param    string        zu bereinigender Text
     * @return   string
     * @access   public
     */
    private function _cleanHolyName($holyName = null)
    {
        $holyName = strtolower( str_replace( array(' ', '-'), '_', $holyName) );
        $holyName = str_replace( array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $holyName );

        return $holyName;
    }

    /**
     * alle Feiertage für Deutschland erzeugen
     *
     * @access   private
     */
    private function _setHolidays()
    {
        $bbt = new DateTime('nov 23');

        $this -> _holidays = array(
			//feiertage bzgl ostern
            'rosenmontag'               => array('name' => 'Rosenmontag'               , 'date' => $this -> _getEaster() -> modify('-48 days') ),
            'fasching'                  => array('name' => 'Fasching'                  , 'date' => $this -> _getEaster() -> modify('-47 days') ),
            'aschermittwoch'            => array('name' => 'Aschermittwoch'            , 'date' => $this -> _getEaster() -> modify('-46 days') ),
            'palmsonntag'               => array('name' => 'Palmsonntag'               , 'date' => $this -> _getEaster() -> modify('last Sunday') ),
            'gruendonnerstag'           => array('name' => 'Gründonnerstag'            , 'date' => $this -> _getEaster() -> modify('-3 days') ),
            'karfreitag'                => array('name' => 'Karfreitag'                , 'date' => $this -> _getEaster() -> modify('-2 days') ),
            'ostersonntag'              => array('name' => 'Ostersonntag'              , 'date' => $this -> _getEaster()),
            'ostermontag'               => array('name' => 'Ostermontag'               , 'date' => $this -> _getEaster() -> modify('+1 day') ),
            'christi_himmelfahrt'       => array('name' => 'Christi Himmelfahrt'       , 'date' => $this -> _getEaster() -> modify('+39 days') ),
            'pfingstsonntag'            => array('name' => 'Pfingstsonntag'            , 'date' => $this -> _getEaster() -> modify('+49 days') ),
            'pfingstmontag'             => array('name' => 'Pfingstmontag'             , 'date' => $this -> _getEaster() -> modify('+50 days') ),
            'fronleichnam'              => array('name' => 'Fronleichnam'              , 'date' => $this -> _getEaster() -> modify('+60 days') ),
            'neujahr'                   => array('name' => 'Neujahr'                   , 'date' => new DateTime('jan 1st') ),
            'heilige_drei_koenige'      => array('name' => 'Heilige Drei Könige'       , 'date' => new DateTime('jan 6') ),

			//feiertage
            'tag_der_arbeit'            => array('name' => 'Tag der Arbeit'            , 'date' => new DateTime('may 1st') ),
            'maria_himmelfahrt'         => array('name' => 'Maria Himmelfahrt'         , 'date' => new DateTime('aug 15') ),
            'tag_der_deutschen_einheit' => array('name' => 'Tag der deutschen Einheit' , 'date' => new DateTime('oct 3') ),
            'reformationstag'           => array('name' => 'Reformationstag'           , 'date' => new DateTime('oct 31') ),
            'allerheiligen'             => array('name' => 'Allerheiligen'             , 'date' => new DateTime('nov 1st') ),
            'buss_und_bettag'           => array('name' => 'Buss und Bettag'           , 'date' => $bbt -> modify('last Wednesday') ),
            'erster_advent'             => array('name' => 'erster Advent'             , 'date' => $this -> _getAdvents(4) ),
            'zweiter_advent'            => array('name' => 'zweiter Advent'            , 'date' => $this -> _getAdvents(3) ),
            'dritter_advent'            => array('name' => 'dritter Advent'            , 'date' => $this -> _getAdvents(2) ),
            'vierter_advent'            => array('name' => 'vierter Advent'            , 'date' => $this -> _getAdvents(1) ),
            'heilig_abend'              => array('name' => 'Heilig Abend'              , 'date' => $this -> _getHeiligAbend() ),
            'weihnachtstag1'            => array('name' => 'erster Weihnachtsfeiertag' , 'date' => new DateTime('dec 25th') ),
            'weihnachtstag2'            => array('name' => 'zweiter Weihnachtsfeiertag', 'date' => new DateTime('dec 26th') ),
			'silvester'                 => array('name' => 'Silvester'                 , 'date' => new DateTime('dec 31') ),

			//lululutage
            'int_holocause_day'         => array('name' => 'Internationaler Tag des Gedenkens an die Opfer des Holocaust', 'date' => new DateTime('jan 27') ),
            'valentinstag'              => array('name' => 'Valentinstag'              , 'date' => new DateTime('feb 14') ),
            'int_muttersprache'         => array('name' => 'Internationaler Tag der Muttersprache', 'date' => new DateTime('feb 21') ),
            
                             );
    }

    /**
     * Ostern des aktuellen Jahres ermitteln
     *
     * @return   datetime
     * @access   public
     */
    private function _getEaster()
    {
        $easter = new DateTime('now');
        $year = $easter->format('Y');

        $easter -> setDate($year, 3, 21);
        $easter -> setTime(0, 0, 0);
        $easter -> modify('+' . easter_days($year) . 'days');

        return $easter;
    }

    /**
     * Heiligabend des aktuellen Jahres ermitteln
     *
     * @return   datetime
     * @access   public
     */
    private function _getHeiligAbend()
    {
        return new DateTime('dec 24');
    }

    /**
     * Advent-Sonntage ermitteln
     *
     * @param    integer      Anzahl der Sonntage
     * @return   datetime
     * @access   public
     */
    private function _getAdvents($counter = 1)
    {
        $advent = $this -> _getHeiligAbend();

        for( $i = 0; $i < $counter; $i++ )
        {
            $advent -> modify('last Sunday');
        }

        return $advent;
    }
}
?>