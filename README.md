# Bad Apple - Pocket Mine (PMMP 2.0.0)

This is a plugin made specifically for rendering Bad Apple in a minecraft world, using chunks as the screen
But you can render pratically any video that you want (only two colors)

There is a video of Bad Apple in the game (recorded on a moto g53 5G):

https://github.com/D4yvid/badapple-pocketmine/assets/115833146/67ddb38f-afe5-4a98-a33f-d0842aa3d735

## Creating the frames

To create the video frames, you'll need `ffmpeg` and `jp2a`.

You'll need to put these frames in the resources/frames directory, AND you'll need to get all these frames and put them in a `frames.txt` inside the directory IN ORDER.
The plugin will lookup this file to find all the video frames.

```sh
resources/frames $ ffmpeg -i ./video.mp4 -vf fps=10 $filename%05d.png
resources/frames $ for i in *.png; do jp2a $i --chars=" #" --size=128x128 > ${i/png/frame}; done
resources/frames $ rm *.png
resources/frames $ ls -1 *.frame > frames.txt
```

## Using the plugin

To start your video playback, just grab a Bone in your inventory and click in the ground with it, it will automatically start the playback
If you have DEBUG enabled in PMMP, it will show a status in the terminal
