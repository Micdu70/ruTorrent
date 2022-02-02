<?php
require_once( '../../php/rtorrent.php' );

$status = null;

if(isset($_REQUEST['result']))
{
    $message = "theUILang.ZIPFinished";

    if (isset($_REQUEST['tot']))
    {
        $message = $message.'+ " ('.$_REQUEST['added'].'/'.$_REQUEST['tot'].')"';
    }
	CachedEcho::send('log('.$message.');',"text/html");
}

$label = null;
if(isset($_REQUEST['label']))
{
    $label = trim($_REQUEST['label']);
}

$dir_edit = null;
if(isset($_REQUEST['dir_edit']))
{
    $dir_edit = trim($_REQUEST['dir_edit']);
    if(!rTorrentSettings::get()->correctDirectory($dir_edit))
    {
        $status = "";
	    CachedEcho::send('log(theUILang.ZIPFailedDirectory);', "text/html");
    }
}

if(is_null($status) && isset($_FILES['torrent_zip']))
{
    $file = $_FILES['torrent_zip'];
    $ufile = $file['name'];
    if(pathinfo($ufile,PATHINFO_EXTENSION)!="zip")
       $ufile.=".zip";
    $ufile = FileUtil::getUniqueUploadedFilename($ufile);
    if(!move_uploaded_file($file['tmp_name'],$ufile))
    {
	    CachedEcho::send('log(theUILang.ZIPErrorOpeningZip);', "text/html");
    }

    $zip = new ZipArchive;
    if (!($zip->open($ufile)))
    {
	    CachedEcho::send('log(theUILang.ZIPErrorOpeningZip);', "text/html");
    }

    $folderpath = FileUtil::getUploadsPath().'/'.pathinfo($ufile, PATHINFO_FILENAME);
    mkdir($folderpath);

    $toadd = array();

    $torrent_count = 0;
    $added_count = 0;

    for ($i=0; $i < $zip->numFiles; $i++)
    {
        $filename_zip = $zip->getNameIndex($i);
        if (preg_match('/.torrent$/', $filename_zip))
        {
            $torrent_count++;
            $filename = basename($filename_zip);

            $file_index = 1;
            while (file_exists($folderpath.'/'.$filename))
            {
                $filename = pathinfo($filename, PATHINFO_FILENAME).'_'.strval($file_index).'.torrent';
                $index++;
            }

            array_push($toadd, $filename);

            copy("zip://".$ufile.'#'.$filename_zip, $folderpath.'/'.$filename);
        }
    }

    $zip->close();
    unlink($ufile);

    foreach ($toadd as $torrent_name)
    {
        $torrent_path = $folderpath.'/'.$torrent_name;

        // Copied almost verbatim from addtorrent.php
        @chmod($torrent_path,$profileMask & 0666);
        $torrent_path = realpath($torrent_path);
        $torrent = new Torrent($torrent_path);
        if($torrent->errors())
            $status = "FailedFile";
        if(!$torrent->errors() && (rTorrent::sendTorrent($torrent_path,
                        !isset($_REQUEST['torrents_start_stopped']),
                        !isset($_REQUEST['not_add_path']),
                        $dir_edit,$label,$saveUploadedTorrents,isset($_REQUEST['fast_resume']))))
        {
            $added_count++;
        }

        unlink($torrent_path);
    }

    rmdir($folderpath);
}

header("HTTP/1.0 302 Moved Temporarily");
header("Location: ".$_SERVER['PHP_SELF'].'?result=0&tot='.$torrent_count.'&added='.$added_count);
?>
