package org.catrobat.web.android;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/**
 * android.media.MediaRecorder for the browser (compat/Rewrite remaps it), with the calls Catroid's SoundRecorder makes
 * for the loudness sensor (SensorLoudness): it records nothing, it listens to the microphone (getUserMedia, which asks
 * for permission) and getMaxAmplitude reports its current peak on Android's scale, 0 to 32767.
 */
public class MediaRecorder {
	private JSObject microphone;

	public void reset() {
	}

	public void setAudioSource(int source) {
	}

	public void setOutputFormat(int format) {
	}

	public void setAudioEncoder(int encoder) {
	}

	public void setOutputFile(String path) {
	}

	public void prepare() {
	}

	public void start() {
		microphone = open();
	}

	public void stop() {
		if (null != microphone) {
			close(microphone);
			microphone = null;
		}
	}

	public void release() {
		stop();
	}

	public int getMaxAmplitude() {
		return null == microphone ? 0 : peak(microphone);
	}

	@JSBody(script = "var m = {};"
			+ "navigator.mediaDevices.getUserMedia({audio: true}).then(function (stream) {"
			+ "  m.stream = stream;"
			+ "  if (m.closed) { stream.getTracks().forEach(function (t) { t.stop(); }); return; }"
			+ "  m.context = new AudioContext();"
			+ "  m.analyser = m.context.createAnalyser();"
			+ "  m.context.createMediaStreamSource(stream).connect(m.analyser);"
			+ "  m.samples = new Float32Array(m.analyser.fftSize);"
			+ "}).catch(function () {});"
			+ "return m;")
	private static native JSObject open();

	@JSBody(params = "m", script = "if (!m.analyser) return 0; m.analyser.getFloatTimeDomainData(m.samples);"
			+ "var peak = 0; for (var i = 0; i < m.samples.length; i++) peak = Math.max(peak, Math.abs(m.samples[i]));"
			+ "return Math.min(32767, Math.round(peak * 32767));")
	private static native int peak(JSObject m);

	@JSBody(params = "m", script = "m.closed = true; if (m.stream) m.stream.getTracks().forEach(function (t) { t.stop(); });"
			+ "if (m.context) m.context.close();")
	private static native void close(JSObject m);
}
