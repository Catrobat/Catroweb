package org.catrobat.web.loader;

import org.teavm.interop.Async;
import org.teavm.interop.AsyncCallback;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSByRef;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

/**
 * The project's sounds, decoded by Web Audio when the project loads, by their Catroid file path. One AudioContext,
 * created by StartScreen inside the start click, because browsers only allow sound after a user gesture. Without
 * Web Audio, or while the context stays suspended, sounds are silent and everything else goes on.
 */
public final class Sounds {
	private Sounds() {
	}

	@JSFunctor
	public interface Callback extends JSObject {
		void run();
	}

	/**
	 * ponytail: decoded sounds stay in memory, at 22050 Hz to halve it (a three-minute stereo song is about 32 MB);
	 * stream long sounds through media elements if projects with many songs run out of memory.
	 */
	@JSBody(script = "var s = window.catrobatSounds || (window.catrobatSounds = {buffers: {}, pending: []});"
			+ "if (!s.context) {"
			+ "  try { s.context = new (window.AudioContext || window.webkitAudioContext)({sampleRate: 22050}); }"
			+ "  catch (e) { console.log('player: no sound: ' + e); return; }"
			+ "  var wake = function () { if (s.context.state !== 'running') s.context.resume(); };"
			+ "  ['pointerdown', 'keydown'].forEach(function (t) { window.addEventListener(t, wake, true); });"
			+ "  document.addEventListener('visibilitychange', function () { if (!document.hidden) wake(); });"
			+ "}"
			+ "if (s.context.state !== 'running') s.context.resume();")
	public static native void createContext();

	/** Starts decoding; a file the browser cannot decode stays silent, like an unplayable file on Android. */
	@JSBody(params = {"path", "bytes"}, script = "var s = window.catrobatSounds; if (!s || !s.context) return;"
			+ "var data = bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength);"
			+ "s.pending.push(new Promise(function (resolve) {"
			+ "  var done = function (b) { if (b) s.buffers[path] = b; resolve(); };"
			+ "  var failed = function (e) { console.log('player: cannot decode ' + path + ': ' + e); resolve(); };"
			+ "  var result = s.context.decodeAudioData(data, done, failed);" // callback form: older Safari has no promise
			+ "  if (result && result.catch) result.catch(failed);"
			+ "}));")
	public static native void decode(String path, @JSByRef byte[] bytes);

	/** Blocks the calling TeaVM thread until every sound passed to decode is decoded (or failed to). */
	@Async
	public static native Void awaitDecoded();

	private static void awaitDecoded(AsyncCallback<Void> decoded) {
		whenDecoded(() -> decoded.complete(null));
	}

	@JSBody(params = "ready", script = "var s = window.catrobatSounds;"
			+ "Promise.all(s ? s.pending : []).then(function () { ready(); });")
	private static native void whenDecoded(Callback ready);

	@JSBody(params = "path", script = "var s = window.catrobatSounds, b = s && s.buffers[path]; return b ? b.duration : 0;")
	public static native double duration(String path);

	/**
	 * Starts the sound and returns its source, or null without one. The page counts started sounds in
	 * data-sounds-started, for scripts/smoke.sh.
	 */
	@JSBody(params = {"path", "offset", "volume"}, script = "var s = window.catrobatSounds, b = s && s.buffers[path];"
			+ "if (!b) return null;"
			+ "var source = s.context.createBufferSource(), gain = s.context.createGain();"
			+ "source.buffer = b; gain.gain.value = volume;"
			+ "source.connect(gain); gain.connect(s.context.destination);"
			+ "source.gainNode = gain;"
			+ "source.start(0, Math.max(0, Math.min(offset, b.duration)));"
			+ "document.body.dataset.soundsStarted = (+(document.body.dataset.soundsStarted || 0) + 1);"
			+ "return source;")
	public static native JSObject play(String path, double offset, float volume);

	@JSBody(params = "source", script = "if (source) { try { source.stop(); } catch (e) {} }")
	public static native void stop(JSObject source);

	@JSBody(params = {"source", "volume"}, script = "if (source) source.gainNode.gain.value = volume;")
	public static native void setVolume(JSObject source, float volume);
}
