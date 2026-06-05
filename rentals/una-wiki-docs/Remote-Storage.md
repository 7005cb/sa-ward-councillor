Remote storage engine allows to store files on remote storage. For now the following storage engines are supported:
- `Local` - files are stored locally in `storage` folder
- `S3` - Amazon S3 compatible storage with signature v2 authorisation
- `S3v4` - Amazon S3 compatible storage with signature v4 authorisation 
- `S3v4alt` - Amazon S3 compatible storage with signature v2 or v4 authorisation, use this storage only when others don't work, since it reuploads file to the storage when file privacy is changed, when v4 signature is used then it will work with Amazon S3 storage only, if other than Amazon S3 storage is used then it will switch to v2 anyway.


Storage engine can be changed in Studio > Settings > Storage:
- **Default storage engine** - storage engine to use by default, when changing this setting, then storage engine is changed for storage objects which don't have any files uploaded, if storage object has some files uploaded then manual files transfer to the new storage engine is required, after manual transfer is completed then manually change engine for the storage object in `engine` field in `sys_objects_storage` table. Note that when transferring data from `Local` to  `S3` or `S3v4` you need to make all files public to make it accessible (existing private files will be marked as public, only newly uploaded files will be marked as private).
- **AWS access key** - access key provided by storage engine, for `S3`, `S3v4` and `S3v4alt` only.
- **AWS secret key** - secret key provided by storage engine, for `S3`, `S3v4` and `S3v4alt` only.
- **AWS bucket** - bucket name, for `S3`, `S3v4` and `S3v4alt` only.
- **AWS custom domain** - custom domain to use for file URLs, requires proper DNS setup on your custom domain, leave empty for default domain from particular storage engine, for `S3` and `S3v4` only.
- **Endpoint** - particular storage endpoint domain, for Amazon leave empty, for Google storage use `storage.googleapis.com` for Wasabi storage use `s3.wasabisys.com`, for `S3`, `S3v4` and `S3v4alt` only.
- **Signature version** - signature version, for `S3v4alt` only, `S3` supports only `v2`, `S3v4` supports only `v4`.
- **Region** - region, such as `us-east-1`, for `S3v4` and `S3v4alt` only.


Not all storage providers works with all storage engines, here is a table to show which one to use:

| Provider      | S3                     | S3v4                   | S3v4alt(v2)            | S3v4alt(v4) |
| ------------- |:----------------------:|:----------------------:|:----------------------:| :----------:|
| Amazon        | OK (some regions FAIL) | OK (some regions FAIL) | OK (some regions FAIL) | OK          |
| Google        | OK                     | FAIL                   | FAIL                   | FAIL        |
| Wasabi        | OK                     | OK                     | OK                     | FAIL        |


How to generate keys:
- [Wasabi](https://wasabi-support.zendesk.com/hc/en-us/articles/360019677192-Creating-a-Wasabi-API-Access-Key-Set)
- [Google](https://cloud.google.com/storage/docs/authentication/managing-hmackeys#console)