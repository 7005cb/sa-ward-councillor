Streams module allows to create live video stream for a wide audience. This module requires separate media server to be setup, currently [Nginx](https://github.com/arut/nginx-rtmp-module) and [OvenMediaEngine](https://www.ovenmediaengine.com/ome) are supported

## Configuration

### Streaming section

* **Server Software** - live streaming server, for now only [Nginx](https://github.com/arut/nginx-rtmp-module) and [OvenMediaEngine](https://www.ovenmediaengine.com/ome) are supported. Nginx doesn't support WebRTC, so you can't broadcast from the browser, for now only OvenMediaEngine supports WebRTC and streaming from the browser. 
* **Server Hostname** - server hostname without protocol, for example: live.example.com
* **Application Name** - application name defined in your streaming server configuration
* **Sources Pattern** - JSON string with possible sources suitable for [OvenMediaPlayer](https://www.ovenmediaengine.com/ovenplayer), there are the following substitution markers are supported: 
  * `{host}` - Server Hostname
  * `{app}` - Application Name
  * `{key}` - Streaming Key
  * `{params}` - Additional Params 
* **Enable MPEG-DASH streaming** - enable this options if MPEG-DASH sources are specified in Sources Pattern
* **Enable HLS streaming** - enable this options if HLS sources are specified in Sources Pattern
* **Base URL for recordings (experimental)** - when recording enabled set base URL where recorded videos can be accessed

***


### OvenMediaEngine

Configuration for [OvenMediaEngine](https://www.ovenmediaengine.com/ome) server.

* **OvenMediaEngine API Key** - base64 string of API key defined in config files
* **OvenMediaEngine API Protocol** - protocol for API requests
* **OvenMediaEngine API Port** - API port defined in config files 
* **OvenMediaEngine Signed Policy Secret Key** - secret key defined in config files, this is optional, but needed to restrict to allow to use streaming server only for users which have permission to stream in UNA
* **Recording source** - `OutputStreamName` from `OutputProfile` section to use for recording, if different from `${OriginStreamName}`, `{key}` replacement marker is supported here

Sample OvenMediaEngine configuration for WebRTC streaming in Server.xml file. If Origin and Edge model is used then specify below configurations for Origin server. 

Enable API Server in `Bind` section:
```xml
<Managers>
    <API>
	<Port>${env:OME_API_PORT:8081}</Port>
	<WorkerCount>1</WorkerCount>
    </API>
<Managers>
```

Enable WebRTC in `Bind > Providers` section:
```xml
<WebRTC>
    <Signalling>
        <Port>${env:OME_SIGNALLING_PORT:3333}</Port>
        <WorkerCount>1</WorkerCount>
        <TLSPort>3334</TLSPort>
    </Signalling>
    <IceCandidates>
        <TcpRelay>${env:OME_TCP_RELAY_ADDRESS:*:3478}</TcpRelay>
        <TcpForce>false</TcpForce>
        <TcpRelayWorkerCount>1</TcpRelayWorkerCount>
        <IceCandidate>${env:OME_ICE_CANDIDATES:*:10006/udp}</IceCandidate>
    </IceCandidates>
</WebRTC>
```

Enable WebRTC in `Bind > Publishers` section:
```xml
<WebRTC>
    <Signalling>
        <Port>${env:OME_SIGNALLING_PORT:3333}</Port>
        <WorkerCount>1</WorkerCount>
        <TLSPort>3334</TLSPort>
    </Signalling>
    <IceCandidates>
        <TcpRelay>${env:OME_TCP_RELAY_ADDRESS:*:3478}</TcpRelay>
        <TcpRelayWorkerCount>1</TcpRelayWorkerCount>
        <IceCandidate>${env:OME_ICE_CANDIDATES:*:10006/udp}</IceCandidate>
    </IceCandidates>
</WebRTC>
```

Modern browsers require secure connection for WebRTC, so OvenMediaEngine need to be configured to use secure TLS protocol. Specify paths to certificates in `VirtualHost` section:
```xml
<Host>
    <Names>
        <Name>live.example.com</Name>
    </Names>
    <TLS>
        <CertPath>/path_here/live.example.com.cer</CertPath>
        <KeyPath>/path_here/live.example.com.key</KeyPath>
        <ChainCertPath>/path_here/fullchain.cer</ChainCertPath>
    </TLS>
</Host>
```

Enable signed policy by specifying `SecretKey` in `SignedPolicy` section, so special signed policy will be required for broadcasting, for consuming stream no policy is needed according to the configuration below, you also need to specify `SecretKey` from here in **OvenMediaEngine Signed Policy Secret Key** setting:
```xml
<SignedPolicy>
	<PolicyQueryKeyName>policy</PolicyQueryKeyName>
	<SignatureQueryKeyName>signature</SignatureQueryKeyName>
	<SecretKey>54321ytrewq_change_to_your_own</SecretKey>
	<Enables>
		<Providers>rtmp,webrtc,srt</Providers>
	</Enables>
</SignedPolicy>
```

Specify application name in `Application` section, or leave it as it is, the same app name need to be specified in **Application Name** configuration:
```xml
<Name>app</Name>
<Type>live</Type>
```

Specify desired output profiles in `VirtualHost > Application > OutputProfiles` section, please note that the following configuration enables transcoding which require considerable server resources, so probably 8 CPU cores are need for the configuration below:
```xml
<OutputProfile>
    <Name>1080p30</Name>
    <OutputStreamName>${OriginStreamName}_1080p30</OutputStreamName>
    <Encodes>
        <Audio><Codec>opus</Codec><Bitrate>128000</Bitrate><Samplerate>48000</Samplerate><Channel>2</Channel></Audio>
        <Video>
            <Codec>h264</Codec>
            <Bitrate>3072000</Bitrate><Framerate>30</Framerate>
            <Width>1920</Width><Height>1080</Height>
        </Video>
    </Encodes>
</OutputProfile>
<OutputProfile>
    <Name>720p30</Name>
    <OutputStreamName>${OriginStreamName}_720p30</OutputStreamName>
    <Encodes>
        <Audio><Codec>opus</Codec><Bitrate>128000</Bitrate><Samplerate>48000</Samplerate><Channel>2</Channel></Audio>
        <Video>
            <Codec>h264</Codec>
            <Bitrate>1536000</Bitrate><Framerate>30</Framerate>
            <Width>1280</Width><Height>720</Height>
        </Video>
    </Encodes>
</OutputProfile>
```

You can disable transcoding and bypass input stream as it is, but it maybe some clients will experience problems with supported formats and result maybe inconsistent, but in this case much slower streaming server can be used:
```xml
<OutputProfile>
    <Name>bypass_stream</Name>
    <OutputStreamName>${OriginStreamName}</OutputStreamName>
    <Encodes>
        <Audio><Bypass>true</Bypass></Audio>
        <Video><Bypass>true</Bypass></Video>
    </Encodes>
</OutputProfile>
```

Enable WebRTC in `VirtualHost > Application > Providers` section:
```xml
<WebRTC />
```

Enable WebRTC in `VirtualHost > Application > Publishers` section:
```xml
<WebRTC>
    <Timeout>30000</Timeout>
    <Rtx>false</Rtx>
    <Ulpfec>false</Ulpfec>
</WebRTC>
```

Make API access token by token only by adding the following section just before closing `</Server>` tag, base64 string of this key need to be specified in **OvenMediaEngine API Key** setting:
```xml
<Managers>
    <Host>
        <Names>
                <Name>*</Name>
        </Names>
    </Host>
    <API>
        <AccessToken>qwerty12345_change_to_your_own</AccessToken>
    </API>
</Managers>
```

**Sources pattern** setting in UNA for above configuration with transcoding would be the following:
```js
[
    {
        type: "webrtc",
        file: "wss://{host}:3334/{app}/{key}_1080p30{params}",
        label: "1080p30"
    },
    {
        type: "webrtc",
        file: "wss://{host}:3334/{app}/{key}_720p30{params}",
        label: "720p30"
    }
]
```

**Sources pattern** for above configuration with bypass stream:
```js
[
    {
        type: "webrtc",
        file: "wss://{host}:3334/{app}/{key}{params}",
        label: "bypass_stream"
    }
]
```

**For recording functionality** add the following code to `VirtualHost > Applications > Application > Publishers` section:
```xml
<FILE>
    <RootPath>/path/to/recorded/videos</RootPath>
    <FilePath>/${Stream}_${Id}.ts</FilePath>
    <InfoPath>/${Stream}_${Id}.xml</InfoPath>
</FILE>
```
<u>NOTE</u>: recording in OvenMediaEngine is still in Beta and it may result in unpredictable result, for best result use `Bypass` mode for recording source and `.ts` file format.

***


### NGINX

Configuration for [NGINX RTMP module](https://github.com/arut/nginx-rtmp-module)

* **NGINX Stats URL** - URL to get statistics about current streaming such as number of viewers
* **NGINX Authentication** - authentication for publishing the stream, `on_publish` setting need to be configured in NGINX, this setting doesn't support `https` URLs.
* **NGINX recording folder path** - when recording enabled, set absolute path to folder where recorded videos are stored, `on_record_done` setting need to be configured in NGINX, this setting doesn't support `https` URLs.


Sample NGINX configuration for HLS streaming (note this configuration will expose your streaming service for everyone):
```
rtmp {
    server {
        listen 1935;

        application app {
            live on;

            record off; # disable recording

            exec ffmpeg -i rtmp://localhost/app/$name
              -preset ultrafast -c:a aac -s 1280:720  -b:a 128k -c:v libx264 -x264-params keyint=24:no-scenecut=1 -r 24 -b:v 1536k -f flv rtmp://localhost/hls/$name_mid
              -preset ultrafast -c:a aac -s 1920:1080 -b:a 128k -c:v libx264 -x264-params keyint=24:no-scenecut=1 -r 24 -b:v 2048K -f flv rtmp://localhost/hls/$name_hi;

            deny play all;
        }

        application hls {
            live on;

            on_publish http://example.com/m/stream/nginx_on_publish/;

            hls on;
            hls_path /tmp/hls;
            hls_nested on;
            hls_fragment 2s;
            hls_playlist_length 10s;

            hls_variant _mid BANDWIDTH=1664000;
            hls_variant _hi  BANDWIDTH=2176000;
        }
    }
}
```


To enable recording replace `record off;` with the following:
```
record all; 
record_path /path/to/recorded/videos;
record_unique on;
on_record_done http://example.com/m/stream/on_record_done/;
``` 


Then add virtual host:
```
        server {
            listen 80;
            server_name  live.example.com;
            index index.html index.htm;

            root /var/www/html;

            location /app {
                types {
                    application/vnd.apple.mpegurl m3u8;
                    video/mp2t ts;
                }
                add_header Access-Control-Allow-Origin *;
                add_header Cache-Control no-cache;
                alias /tmp/hls;
            }
            location /stat {
                rtmp_stat all;
            }
        }
```

**Sources pattern** for above configuration:
```js
[
    {
        type: "hls",
        file: "http://{host}/{app}/{key}_hi/index.m3u8{params}",
        label: "HD 1080"
    },
    {
        type: "hls",
        file: "http://{host}/{app}/{key}_mid/index.m3u8{params}",
        label: "HD 720"
    }
]
```

**Optional reverse proxy** for `on_publish` and `on_record_done` URLs, in case if it need to be enabled on `http` address and/or other subdomain:
```
    location /on_publish/ { # or /on_record_done/
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";

        proxy_set_header X-Real-IP $remote_addr;

        proxy_pass https://example.com/m/stream/nginx_on_publish/; # or https://example.com/m/stream/on_record_done/
        proxy_redirect   off;
    }
```
for example if you configure reverse proxy on some.example.com domain then need to change `on_publish` setting:
```
on_publish http://some.example.com/on_publish/;
```
and
```
on_record_done http://some.example.com/on_record_done/;
```

**Additional notes regarding recording** actual for both NGINX RTMP module and OvenMediaEngine

- Recording folder need to be configured in your web-server to be publicly accessible
- Recorded videos need to be pruned manually, since videos are copied to UNA so it can be deleted shortly after recording, you can setup the following cronjob to delete recordings older than 1 day:
```
45 * * * * find /path/to/recorded/videos/ -mtime +0 -exec rm -f {} \+
```
