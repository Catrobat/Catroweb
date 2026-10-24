package org.catrobat.web.android;

import org.catrobat.web.loader.Sounds;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/**
 * android.media.MediaPlayer for the browser (compat/Rewrite remaps it), with the calls Catroid's SoundManager makes.
 * Plays the sounds Sounds decoded when the project loaded, so prepare() has nothing left to do. Playing and position
 * follow the wall clock, not the audio clock: a context the browser keeps suspended is silent, like a muted phone,
 * but "play sound and wait" still ends and the players get free again.
 */
public class MediaPlayer {
	private String path;
	private JSObject source;
	private boolean playing;
	private double offset;
	private double startedAt;
	private float volume = 1f;

	public void setDataSource(String path) {
		this.path = path;
	}

	public void prepare() {
	}

	public void seekTo(int milliseconds) {
		offset = Math.max(0, milliseconds / 1000.0);
	}

	public void start() {
		stopSource();
		source = Sounds.play(path, offset, volume);
		playing = offset < Sounds.duration(path);
		startedAt = now();
	}

	public void pause() {
		offset = position();
		stopSource();
	}

	public void stop() {
		stopSource();
		offset = 0;
	}

	public void reset() {
		stop();
		path = null;
	}

	public void release() {
		reset();
	}

	public boolean isPlaying() {
		if (playing && position() >= Sounds.duration(path)) {
			playing = false;
		}
		return playing;
	}

	public int getDuration() {
		return (int) Math.round(Sounds.duration(path) * 1000);
	}

	public int getCurrentPosition() {
		return (int) Math.round(position() * 1000);
	}

	public void setVolume(float left, float right) {
		volume = left; // Catroid always passes the same value for both
		Sounds.setVolume(source, volume);
	}

	private double position() {
		return playing ? Math.min(offset + now() - startedAt, Sounds.duration(path)) : offset;
	}

	private void stopSource() {
		playing = false;
		Sounds.stop(source);
		source = null;
	}

	@JSBody(script = "return performance.now() / 1000;")
	private static native double now();
}
