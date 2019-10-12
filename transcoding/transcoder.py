import ffmpy3
import os
import ffprobe3
import subprocess
from ffprobe3 import FFProbe

# Standard 16:9 resolutions, used for comparison with the resolution of the current video
resolutions = [
        360 * 640,
        854 * 480, 
        1280 * 720, 
        1920 * 1080
    ]

# Dictionnary used to get the name of a resolution (used for naming the videos)
resolutionNames = {
        360 * 640: '360p',
        854 * 480: '480p',
        1280 * 720: '720p',
        1920 * 1080: '1080p'
    }

# Dictionnary containing association between a given 16:9 resolution and the corresponding ffmpeg command
# -y enables overwriting (if previous computing has failed)
# -b:v defines the video bitrate
# -maxrate defines the maximum bitrate (usually same as bitrate)
# -bufsize defines the size of the buffer (usually twice as bitrate)
# -vf defines the resolution, the value for the scale argument is the classic 16:9 resolution height and a 
#      negative number which means select the best value to keep the original video ratio. (-2 is used to 
#      have an even number for the width)
# -b:a is for audio bitrates
# -c:a copy allows to keep the same audio codec for both original and computed video
resolutionComputing = {
        1920 * 1080: '-y -b:v 4000k -maxrate 4000k -bufsize 8000k -vf scale=-2:1080 -b:a 192k -c:a copy ',
        1280 * 720: '-y -b:v 2500k -maxrate 2500k -bufsize 5000k -vf scale=-2:720 -b:a 192k -c:a copy ',
        854 * 480: '-y -b:v 1500k -maxrate 1500k -bufsize 3000k -vf scale=-2:480 -b:a 128k -c:a copy ',
        640 * 360: '-y -b:v 700k -maxrate 700k -bufsize 1400k -vf scale=-2:360 -b:a 128k -c:a copy '
    }


# Take the path of the video to convert
# Transcode the video to mp4 format
# Saves the video to the specified path
# ffmpeg must be installed on the server in order for this function to work
# Convert any input video to:
# Container:  MPEG-4
# Video Codec: H.264 (-c:v)
# Audio Codec: MP3 (-c:a)
# -y enables overwriting
def transcodeToMp4(originalPath, toMp4Path):
    toMp4Task = ffmpy3.FFmpeg(
        inputs={originalPath: None},
        outputs={toMp4Path: '-y -c:v h264 -c:a libfdk_aac '}
    )
    toMp4Task.run()


# Take the path of the video to convert
# Transcode the video to webm format
# Saves the video to the specified path
# ffmpeg must be installed on the server in order for this function to work
# Convert any input video to:
# Container:  WebM
# Video Codec: VP9
# Audio Codec: Opus
#'ffmpeg -i input.ext -c:a opus -c:v webm output.webm'

def transcodeToWebm(originalPath, toWebmPath):
    toWebmTask = ffmpy3.FFmpeg(
        inputs={originalPath: None},
        outputs={toWebmPath: '-y -c:v vp9 -c:a libopus '}
    )
    toWebmTask.run()

def extractThumbnail(originalPath, thumbnailPath):
    time = 0.0
    metadata = FFProbe(originalPath)
    for stream in metadata.streams:
        if stream.is_video():
            time = stream.duration_seconds()/2
    thumbnailTask = ffmpy3.FFmpeg(
        inputs={originalPath: None},
        outputs={thumbnailPath: '-y -ss %d -vframes 1 ' % (time) }
    )
    thumbnailTask.run()


# Compute for a given video, every 16:9 resolutions (contained in the resolutions array) lower than
# the current video resolution
# This function requires ffprobe and ffmpeg in order to work  
def computeResolution(inputPath, outputPath, videoName, videoExtension):
    metadata = FFProbe(inputPath)
    videoRes = 0
    videoMaxResolution = 0
    for stream in metadata.streams:
        if stream.is_video():
            (width, height) = stream.frame_size()
            videoRes = width * height
    
    for res in resolutions:
        if videoRes >= res:
            videoMaxResolution = res
            fmpg = ffmpy3.FFmpeg(
                inputs={inputPath: None},
                outputs={outputPath+'/'+videoName+'_'+resolutionNames[res]+'.'+videoExtension : resolutionComputing[res]}
            )
            fmpg.run()
    if videoMaxResolution == 0:
        return videoMaxResolution
    return "_"+resolutionNames[videoMaxResolution]
