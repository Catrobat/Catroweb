# Inert audio fixtures

These synthetic 80 ms 440 Hz tones contain no user recordings. They exercise MIME detection using real WAV, MP3, Opus/Ogg, M4A, MP4/AAC, ADTS/AAC and FLAC bytes. The MP4/AAC fixture represents Catroid's MPEG-4/AAC recorder output (not an Android-device recording). No FFmpeg dependency is needed to run the tests.

Generated with FFmpeg using `-f lavfi -i 'sine=frequency=440:sample_rate=8000:duration=0.08' -map_metadata -1 -c:a ENCODER -threads 1`, with encoders `pcm_s16le`, `libmp3lame`, `libopus`, `aac` (M4A/MP4/AAC) and `flac`. Each fixture was decoded successfully before committing.

Catroid preserves resolved media filenames; its sound recorder uses the extensionless resource string `recording`, and unknown URI names fall back to `.wav` without transcoding. `SoundRecorder.kt` selects `MPEG_4` and `AAC`. `SpriteActivity.addLookFromUri` also preserves resolved image names, while `StorageOperations.copyUriToDir` converts WebP to PNG without renaming. Tests cover these cases and keep other raster-extension mismatches rejected.
