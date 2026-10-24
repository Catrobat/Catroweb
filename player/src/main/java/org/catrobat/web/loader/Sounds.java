package org.catrobat.web.loader;

import org.teavm.interop.Async;
import org.teavm.interop.AsyncCallback;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSByRef;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

/**
 * The project's sounds, decoded by Web Audio when the project loads, by their Catroid file path. One AudioContext,
 * created by StartScreen inside the start click, because browsers only allow sound after a user gesture.
 */
public final class Sounds {
	private Sounds() {
	}

	@JSFunctor
	public interface Callback extends JSObject {
		void run();
	}

	@JSBody(script = "var s = window.catrobatSounds || (window.catrobatSounds = {buffers: {}, pending: []});"
			+ "if (!s.context) s.context = new (window.AudioContext || window.webkitAudioContext)();"
			+ "if (s.context.state === 'suspended') s.context.resume();")
	public static native void createContext();

	/** Starts decoding; a file the browser cannot decode stays silent, like an unplayable file on Android. */
	@JSBody(params = {"path", "bytes"}, script = "var s = window.catrobatSounds;"
			+ "var data = bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength);"
			+ "s.pending.push(s.context.decodeAudioData(data).then(function (b) { s.buffers[path] = b; },"
			+ "  function (e) { console.log('player: cannot decode ' + path + ': ' + e); }));")
	public static native void decode(String path, @JSByRef byte[] bytes);

	/** Blocks the calling TeaVM thread until every sound passed to decode is decoded (or failed to). */
	@Async
	public static native Void awaitDecoded();

	private static void awaitDecoded(AsyncCallback<Void> decoded) {
		whenDecoded(() -> decoded.complete(null));
	}

	@JSBody(params = "ready", script = "Promise.all(window.catrobatSounds.pending).then(function () { ready(); });")
	private static native void whenDecoded(Callback ready);

	@JSBody(params = "path", script = "var b = window.catrobatSounds.buffers[path]; return b ? b.duration : 0;")
	public static native double duration(String path);

	@JSBody(script = "return window.catrobatSounds.context.currentTime;")
	public static native double now();

	@JSBody(params = {"path", "offset", "volume", "ended"}, script = "var s = window.catrobatSounds, b = s.buffers[path];"
			+ "if (!b) return null;"
			+ "var source = s.context.createBufferSource(), gain = s.context.createGain();"
			+ "source.buffer = b; gain.gain.value = volume;"
			+ "source.connect(gain); gain.connect(s.context.destination);"
			+ "source.gainNode = gain; source.onended = function () { ended(); };"
			+ "source.start(0, Math.min(offset, b.duration));"
			+ "return source;")
	public static native JSObject play(String path, double offset, float volume, Callback ended);

	@JSBody(params = "source", script = "if (source) { source.onended = null; try { source.stop(); } catch (e) {} }")
	public static native void stop(JSObject source);

	@JSBody(params = {"source", "volume"}, script = "if (source) source.gainNode.gain.value = volume;")
	public static native void setVolume(JSObject source, float volume);
}
