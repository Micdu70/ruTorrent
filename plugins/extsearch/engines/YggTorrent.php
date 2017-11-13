<?php

class YggTorrentEngine extends commonEngine
{
    const URL = 'https://yggtorrent.com';
    const MAX_PAGE = 10;
    const PAGE_SIZE = 25;

    public $defaults = array("public" => false, "page_size" => self::PAGE_SIZE, 'auth' => 1);

    // No search filters for now
    public $categories = array(
        'Tout' => '',
    );

    protected static $seconds = array
    (
        'seconde'	=>1,
        'minute'	=>60,
        'heure'		=>3600,
        'jour'		=>86400,
        'mois'		=>2592000,
        'an'		=>31536000
    );

    protected static function getTime( $now, $ago, $unit )
    {
        $delta = (array_key_exists($unit,self::$seconds) ? self::$seconds[$unit] : 0);
        return( $now-($ago*$delta) );
    }

    private $category_mapping = array(
        'filmvidéo' => 'Vidéos',
        'série-tv' => 'Séries',
        'animation-série' => 'Animation',
        'jeu-vidéo' => 'Jeux',
        'emission-tv' => 'Emission TV',
        'vidéo-clips' => 'Clip Vidéo',
        'bds' => 'Bande dessinée'
    );

    public function action($what, $cat, &$ret, $limit, $useGlobalCats)
    {
        $added = 0;
        $what = rawurlencode(rawurldecode($what));

        // Initial search to retrieve the page count
        $search = self::URL . '/engine/search?q=' . $what;
        $cli = $this->fetch($search);

        // Check if we have results
        if (($cli == false) || (strpos($cli->results, "download_torrent") === false)) {
            return;
        }

        // Check if there is only one page
        if (strpos($cli->results, '<ul class="pagination">') === false) {
            $maxPage = 1;
        } else {
            // Retrieve the page count
            $nbRet = preg_match_all('`data-ci-pagination-page="(?P<page>\d+)"`', $cli->results, $retPage);
            if ($nbRet) {
                $nbPage = max($retPage['page']);
                $maxPage = $nbPage < self::MAX_PAGE ? $nbPage : self::MAX_PAGE;
            } else {
                return;
            }
        }

        for ($page = 1; $page <= $maxPage; $page++) {
            // We already have results for the first page
            if ($page !== 1) {
                $pg = ($page - 1) * self::PAGE_SIZE;
                $search = self::URL . '/engine/search?q=' . $what . '&page=' . $pg;
                $cli = $this->fetch($search);
            }

            $res = preg_match_all(
                '`<tr>.*<a class="torrent-name" href="(?P<desc>.*)">(?P<name>.*)</a>' .
                '.*<a.*/download_torrent\?id=(?P<id>.*)">.*<td><i.*>.*</i>.*(?P<ago>\d+) (?P<unit>(seconde|minute|heure|jour|mois|an)).*</td>.*<td>(?P<size>.*)</td>' .
                '.*<td.*>(?P<seeder>.*)</td.*>.*<td.*>(?P<leecher>.*)</td.*>.*</tr>`siU',
                $cli->results,
                $matches
            );

            if ($res) {
                $now = time();
                for ($i = 0; $i < $res; $i++) {
                    $link = self::URL . "/engine/download_torrent?id=" . $matches["id"][$i];
                    if (!array_key_exists($link, $ret)) {
                        $item = $this->getNewEntry();
                        $item["desc"] = $matches["desc"][$i];
                        $item["name"] = self::removeTags($matches["name"][$i]);

                        // The parsed size has the format XX.XXGB, we need to add a space to help a bit the formatSize method
                        $item["size"] = self::formatSize(preg_replace('/([0-9.]+)(\w+)/', '$1 $2', $matches["size"][$i]));

                        // To be able to display categories, we need to parse them directly from the torrent URL
                        $cat = preg_match_all('`' . self::URL . '/torrent/(?P<cat1>.*)/(?P<cat2>.*)/`', $item['desc'], $catRes);
                        if ($cat) {
                            $cat1 = $this->getPrettyCategoryName($catRes['cat1'][0]);
                            $cat2 = $this->getPrettyCategoryName($catRes['cat2'][0]);
                            $item["cat"] = $cat1 . ' > ' . $cat2;
                        }

                        // We only have the time since the upload, so let's try to convert that...
                        $item["time"] = self::getTime( $now, $matches["ago"][$i], $matches["unit"][$i] );

                        $item["seeds"] = intval(self::removeTags($matches["seeder"][$i]));
                        $item["peers"] = intval(self::removeTags($matches["leecher"][$i]));
                        $ret[$link] = $item;
                        $added++;
                        if ($added >= $limit) {
                            return;
                        }
                    }
                }
            } else {
                break;
            }
        }
    }

    private function getPrettyCategoryName($input)
    {
        if (array_key_exists($input, $this->category_mapping)) {
            return $this->category_mapping[$input];
        } else {
            return ucwords($input);
        }
    }
}