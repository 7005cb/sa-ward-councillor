Video transcoder simplifies video conversion, extracting images from video and storing converted files. Main purpose is to resize and convert videos into different formats to play in all modern browsers using HTML5 video. 

Conversion is performed using build-in ffmpeg (by default linux binary). It's possible to specify custom path to ffmpeg in `inc/header.inc.php` file:
```php
define('BX_SYSTEM_FFMPEG', '/path/to/plugins/ffmpeg/ffmpeg.exe'); ///< path to ffmpeg binary
```

To generate video which plays in all moders browsers along with video poster, you need to create 3 different video transcoding objects which will generate .mp4, .webm videos and video poster. Video for conversion is queued and when cron is run (usually every minute) video conversion is performed.

## Adding transcoder object

[[Transcoder object|Images-Transcoder#adding-new-images-transcode-object]], [[source types|Images-Transcoder#source-types]], [[automatic deletion|Images-Transcoder#automatic-deletion]] and [[filters|Images-Transcoder#filters]] works the same way as in [[Images transcoder|Images-Transcoder]]. But there are several exceptions:
- it is highly recommended to disable `atime_pruning` and `atime_tracking`, or set it to fairly big value, since video transcoding is not performed on the fly and takes some time.
- video is converting upon first access, so it is probably better to force video conversion by calling `BxDolTranscoderVideo::getFileUrl` just after video uploading.
- while video is pending for conversion or in the process then `BxDolTranscoderVideo::getFileUrl` methods returns empty string for video and predefined image for video poster.


## Remote video transcoding

Video conversion can be performed on separate server or multiple servers, to do it:
- remote transcoding can be used only with **remote storage** enabled
- install UNA on separate server(s), but connect to the same DB which your main site is using
- enable 'Remote video transcoding' option (when it is enabled it takes a little longer to convert videos)
- add the following code to the begining of inc/header.inc.php file on the main site, where your actual site in installed:
    ```php
    define('BX_TRANSCODER_PROCESS_COMPLETED', '');
    ```
- if you don't want your main site to convert videos, so all conversion will be performed on the separate server, then add the following code to the begining of `inc/header.inc.php` file on the main site:
    ```php
    define('BX_TRANSCODER_NO_TRANSCODING', '');
    ```
- if you have UNA 12.0.2 or newer then add the following line to `inc/header.inc.php` on the server which is setup for transcoding only:
    ```php
    define('BX_CRON_FILTER', array('sys_transcoder')); 
    ```
    It's needed to make transcoder server to process videos only, without this line all other transcoder job s will be run, which will lead to some undesired effects.
- all servers must have different host name
- only main server must be used as site, additional sites are just for video conversion, don't perform any action on these sites

## Filters

- `Mp4` - this filter convert video into .mp4 format along with resizing, the parameters are the following:
    - `h` - height of resulted video (360px by default), for video it is highly recommended to specify only height parameter (without width parameter), since videos have different aspect ratio, when only height only is specified, then width is automatically calculated using aspect ration of original video
    - `video_bitrate` - video bitrate (512k by default)
    - `audio_bitrate` - video bitrate (128k by default)
    - `ffmpeg_options` - additional command line options for ffmepeg, as key => value array (empty by default)
- `Webm` - this filter convert video into .webm format along with resizing, the parameters are the same as for Mp4 filter
- `Poster` - this filter generates video thumbnail, it tries to get poster at 0, 3 and 5 seconds from the beginning and gets the first not fully black/white thumb


## Example of usage

```php
bx_import('BxTemplFunctions');
bx_import('BxDolTranscoder');

// transcoder objects which generate .mp4, .webm videos and image poster
$oTranscoderMp4 = BxDolTranscoder::getObjectInstance('bx_video_mp4'); 
$oTranscoderWebm = BxDolTranscoder::getObjectInstance('bx_video_webm');
$oTranscoderPoster = BxDolTranscoder::getObjectInstance('bx_video_poster');

// make sure to call it only once (for example: during module installation), before the first usage, no need to call it every time
$oTranscoderMp4->registerHandlers(); 
$oTranscoderWebm->registerHandlers(); 
$oTranscoderPoster->registerHandlers(); 

// get URLs of transcoded videos and video thumbnail, 33 is ID of original video file stored in specified storage object
$sUrlMp4 = $oTranscoderMp4->getFileUrl(33);
$sUrlWebM = $oTranscoderWebm->getFileUrl(33);
$sUrlPoster = $oTranscoderPoster->getFileUrl(33);

echo 'My cat:' . BxTemplFunctions::getInstance()->videoPlayer($sUrlPoster, $sUrlMP4, $sUrlWebM);

```