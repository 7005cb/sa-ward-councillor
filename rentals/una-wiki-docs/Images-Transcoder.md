This API automatically resize and store images, the whole procedure is very simplified.

The advantages of this system:

- **Less code to write** - so you can concentrate on the main functionality
- **Less space usage** - only usable data is transcoded and unused data can be automatically deleted
- **Flexibility** - you can change image filters (for example, new image dimentions) and all images will be transcoded automatically, upon first access
- **Security** - the transcoded image can have the same security setting as original

Images Transcoder is working together with **Storage Engine** to store transcoded data. So, for example, you can store transcoded data on Amazon S3 with reduced redundancy option to save some money.

## Adding new images transcode object

To add image transcoder object add record to `sys_objects_transcoder` table:

- `object` - name of the transcoder object, in the format: vendor prefix, underscore, module prefix, underscore, image size name; for example: `bx_images_thumb`.
- `storage_object` - name of the storage object to store transcoded data, the specified storage object need to be created too.
- `source_type` - type of the source, where is source image is taken from, available options `Storage` and `Folder` for now.
- `source_params` - `source_type` params, each `source_type` can have own set of params, please read futher for more info about particular `source_types`, serialized array of params is stored here.
- `private` - how to store transcoded data:
    - `no` - store transcoded data publicly.
    - `yes` - store transcoded data privately.
    - `auto` - detect automatically, not supported for Folder source type.
- `atime_tracking` - track last access time to the transcoded data, allowed values `0` - disables or `1` - enabled.
- `atime_pruning` - prune transcoded images by last access time, if last access time of the image is older than `atime_pruning` seconds - it is deleted, it works when `atime_tracking` is enabled
- `ts` - unix timestamp of the last change of transcoder parameters, if transcoded image is older than this value - image is deleted and transcoded again.


## Source types

- `Folder` - this source type is some folder with original images for the transcoding, the identifier of the image (handler) is file name. 

    The params are the following:
    - `path` - path to the folder with original images

    This source type has some limitation:
    - automatic detection of private files is not supported
    - transcoded file is not automaticlaly deleted/renewed if original file is changed

- `Storage` - the source of original files is Storage engine, the identifier of the image (handler) is file id. 

    The params are the following:
    - `object` - name of the Storage object


## Filters

- `Resize` - this filter resizes original image, the parameters are the following:
    - `w` - width of resulted image.
    - `h` - height of resulted image.
    - `square_resize` - make resulted image square, even of original image is not square, w and h parameters must be the same.
    - `crop_resize` - crop image to destination size with filling whole area of destination size.
    - `force_type` - always change type of the image to the specified type: png, jpg or gif.

- `Grayscale` - make image grayscale, there is no parameters for this filter


## Automatic deletion

Automatic deletetion of associated data is supported - in the case if original or transcoded file is deleted. All you need is to register alert handlers, just call `registerHandlers ()` function to register handler (for example, during module installation) and call `unregisterHandlers ()` function to unregister handlers (for example, during module uninstallation).

## Example of usage

```php
bx_import('BxDolTranscoderImage');
$oTranscoder = BxDolTranscoderImage::getObjectInstance('bx_images_thumb'); // change images transcode object name to your own
$oTranscoder->registerHandlers(); // make sure to call it only once! before the first usage, no need to call it every time
$sTranscodedImageUrl = $oTranscoder->getImageUrl('my_dog.jpg'); // the name of file, in the case of 'Folder' storage type this is filename
echo 'My dog : <img src="' . $sTranscodedImageUrl . '" />'; // transcoded(resized and/or grayscaled) image will be shown, according to the specified filters
```

## Demo

https://www.youtube.com/watch?v=nlem3b10N9c