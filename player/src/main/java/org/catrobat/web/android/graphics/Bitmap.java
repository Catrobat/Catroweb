package org.catrobat.web.android.graphics;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSByRef;
import org.teavm.jso.JSObject;

import java.io.IOException;
import java.io.OutputStream;

/**
 * android.graphics.Bitmap for Catroid's text and bubble actors (compat/Rewrite remaps it there only): an HTML canvas,
 * with what ShowTextActor and ShowBubbleActor call.
 */
public final class Bitmap {
	public enum Config {
		ARGB_8888
	}

	public enum CompressFormat {
		PNG
	}

	final JSObject canvas;
	private final int width;
	private final int height;

	private Bitmap(int width, int height) {
		// Android refuses 0; a canvas has at least one pixel, and the sizes must match it for the texture upload.
		this.width = Math.max(1, width);
		this.height = Math.max(1, height);
		canvas = createCanvas(this.width, this.height);
	}

	public static Bitmap createBitmap(int width, int height, Config config) {
		return new Bitmap(width, height);
	}

	public int getWidth() {
		return width;
	}

	public int getHeight() {
		return height;
	}

	public void recycle() {
	}

	public boolean compress(CompressFormat format, int quality, OutputStream stream) {
		try {
			stream.write(png(canvas));
			return true;
		} catch (IOException e) {
			return false;
		}
	}

	/** The pixels as RGBA bytes, row by row from the top, not premultiplied (canvas getImageData). */
	public byte[] rgba() {
		return rgba(canvas, width, height);
	}

	@JSBody(params = {"width", "height"}, script = "var c = document.createElement('canvas');"
			+ "c.width = width; c.height = height;"
			+ "c.getContext('2d', {willReadFrequently: true});" // shown variables read their pixels back every frame
			+ "return c;")
	private static native JSObject createCanvas(int width, int height);

	@JSBody(params = "canvas", script = "var data = atob(canvas.toDataURL('image/png').split(',')[1]);"
			+ "var bytes = new Int8Array(data.length);"
			+ "for (var i = 0; i < data.length; i++) bytes[i] = data.charCodeAt(i);"
			+ "return bytes;")
	@JSByRef
	private static native byte[] png(JSObject canvas);

	@JSBody(params = {"canvas", "width", "height"}, script = "var data = canvas.getContext('2d').getImageData(0, 0, width, height).data;"
			+ "return new Int8Array(data.buffer, data.byteOffset, data.length);")
	@JSByRef
	private static native byte[] rgba(JSObject canvas, int width, int height);
}
