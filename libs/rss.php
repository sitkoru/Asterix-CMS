<?php
/**
 * Rss Feed Reader
 *
 * After two years, I decided to rewrite and fix this
 * class. Thanks to all of you who sent me bug reports
 * and sorry that I didn't replied. I'm lazy when it
 * comes to talking.
 *
 * Changelog v2.1:
 * - added Text parser support. Got this idea today
 * while working on something else. Maybe help in
 * some cases where XML and SimpleXML fails. To be
 * honest, it's not really the finest solution but
 * it works and I was too tired to think more about
 * it...
 *
 * Changelog v2.0:
 * - new version, hopefully without stupid bugs
 * if left in first version :)
 * - added SimpleXML parser support
 *
 * Bugs:
 *     - Opening local files won't work for some reason,
 *     probably because of headers or something. Using
 * SXML or TXT instead of XML may help.
 *
 * Issues:
 * - Because of some PHP configurations deny to open
 * remote files with fopen(), I used file_get_contents()
 * but you still can switch to fopen(). See code for
 * more informations.
 *
 * Usage:
 * - See example.php
 *
 * Notes:
 * - If you study this code a little, you can see that
 * SXML is just a little wrapper around very few lines
 * of code. SimpleXML is really nice and simple way to
 * parse XML, but it has problems with special chars
 * entities.
 * - Even that this class is released under GPL licence,
 * I'm not responsible for anything that happens to you
 * (e.g. you die by reading these dull lines of text)
 * or your computer/website/whatever.
 *
 * Copyright 2007-2009, Daniel Tlach
 *
 * Licensed under GNU GPL
 *
 * @copyright        Copyright 2007-2009, Daniel Tlach
 * @link            http://www.danaketh.com
 * @version            2.1
 * @license            http://www.gnu.org/licenses/gpl.txt
 */
class Rss
{

    private $parser;
    private $feed_url;
    private $item;
    private $tag;
    private $output;
    private $counter = 0;

    private $title = NULL;
    private $description = NULL;
    private $link = NULL;
    private $pubDate = NULL;

    // {{{ construct
    /**
    * Constructor
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    */
    function __construct(  )
    {
    }
    // }}}

    // {{{ getFeed
    /**
    * Get RSS feed from given URL, parse it and return
    * as classic array. You can switch between XML
    * and SimpleXML method of reading.
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         public
    * @param        string $url Feed URL
    * @param        constant $method Reading method
    */
    public function read($url, $method = 'XML')
    {
        $this->counter = 0;
        switch($method)    {
            case 'TXT':
                return $this->txtParser($url);
                break;
            case 'SXML':
                return $this->sXmlParser($url);
                break;
            default:
            case 'XML':
                return $this->xmlParser($url);
                break;
        }
    }
    // }}}

    // {{{ sXmlParser
    /**
    * Parser for the SimpleXML way.
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        string $url Feed URL
    * @return        array $feed Array of items
    */
    private function sXmlParser($url)
    {
        $xml = simplexml_load_file($url);
        foreach($xml->channel->item as $item)    {
            $this->output[$this->counter]['title'] = $item->title;
            $this->output[$this->counter]['description'] = $item->description;
            $this->output[$this->counter]['link'] = $item->link;
            $this->output[$this->counter]['date'] = $item->pubDate;
            $this->counter++;
        }

        return $this->output;
    }
    // }}}

    // {{{ xmlParser
    /**
    * Parser for the XML way.
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        string $url Feed URL
    * @return        array $feed Array of items
    */
    private function xmlParser($url)
    {
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        $this->feed_url = $url;
        @xml_set_object($this->parser,&$this);
        xml_set_element_handler($this->parser, "xmlStartElement", "xmlEndElement");
        xml_set_character_data_handler($this->parser, "xmlCharacterData");

        $this->xmlOpenFeed();

        return $this->output;
    }
    // }}}

    // {{{ txtParser
    /**
    * Parser for the Text way.
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        string $url Feed URL
    * @return        array $feed Array of items
    */
    private function txtParser($url)
    {
        //* Comment following lines
        $feed = file_get_contents($url);
        /*/
        /* And uncomment these
        $feed = '';
        $fh = fopen($url, 'r');
        while(!feof($fh))    {
            $feed .= fread($fh, 4096);
        } // while
        */

        $this->txtParseFeed($feed);

        return $this->output;
    }
    // }}}

    // {{{ xmlOpenFeed
    /**
    * Parser for the XML way.
    * I used file_get_contents() as a method to get
    * content of the feed, because I found that some
    * have denied opening remote files with fopen().
    * Tho you still can try switch functions if you
    * like...
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @return        void
    */
    private function xmlOpenFeed()
    {
        //* Comment following lines
        $feed = file_get_contents($this->feed_url);
        xml_parse($this->parser, $feed, TRUE);
        /*/
        /* And uncomment these
        $fh = fopen($this->feed_url, 'r');
        while($feed = fread($fh, 4096))    {
            xml_parse($this->parser, $feed, feof($fh));
        } // while
        */
        xml_parser_free($this->parser);
    }
    // }}}

    // {{{ xmlStartElement
    /**
    * Item start
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        object $parser Parser reference
    * @param        string $tag Tag
    * @return        void
    */
    private function xmlStartElement($parser, $tag)
    {
        if ($this->item === TRUE)    {
            $this->tag = $tag;
        }
        else if ($tag === "ITEM")    {
            $this->item = TRUE;
        }
    }
    // }}}

    // {{{ xmlCharacterElement
    /**
    * Item content
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        object $parser Parser reference
    * @param        string $data Content data
    * @return        void
    */
    private function xmlCharacterData($parser, $data)
    {
        if ($this->item === TRUE)    {
            // read the content tags
            switch ($this->tag)    {
                case "TITLE":
                    $this->title .= $data;
                    break;
                case "DESCRIPTION":
                    $this->description .= $data;
                    break;
                case "LINK":
                    $this->link .= $data;
                    break;
                case "PUBDATE":
                    $this->pubDate .= $data;
                    break;
            }
        }
    }
    // }}}

    // {{{ xmlEndElement
    /**
    * Item end
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        object $parser Parser reference
    * @param        string $tag Tag
    * @return        void
    */
    function xmlEndElement($parser, $tag)
    {
        if ($tag == 'ITEM')    {
            $this->output[$this->counter]['title'] = trim($this->title);
            $this->output[$this->counter]['description'] = trim($this->description);
            $this->output[$this->counter]['link'] = trim($this->link);
            $this->output[$this->counter]['date'] = trim($this->pubDate);
            $this->counter++;
            $this->title = NULL;
            $this->description = NULL;
            $this->link = NULL;
            $this->pubDate = NULL;
            $this->item = FALSE;
        }
    }
    // }}}

    // {{{ txtParseFeed
    /**
    * Parse feed using regexp
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    * @access         private
    * @param        string $feed Feed string
    * @return        void
    */
    private function txtParseFeed($feed)
    {
        $feed = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $feed);
        $feed = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $feed);
        preg_match_all('|<item>(.*)</item>|U', $feed, $m);
        foreach($m[1] as $item)    {
            preg_match('|<title>(.*)</title>|U', $item, $title);
            preg_match('|<link>(.*)</link>|U', $item, $link);
            preg_match('|<description>(.*)</description>|U', $item, $description);
            preg_match('|<pubDate>(.*)</pubDate>|U', $item, $pubdate);
            $this->output[$this->counter]['title'] = $title[1];
            $this->output[$this->counter]['description'] = $description[1];
            $this->output[$this->counter]['link'] = $link[1];
            $this->output[$this->counter]['date'] = $pubdate[1];
            $this->counter++;
        }
    }
    // }}}

    // {{{ destruct
    /**
    * Destructor
    *
    * @author        Daniel Tlach <mail@danaketh.com>
    */
    function __destruct()
    {
    }
    // }}}

}

?>