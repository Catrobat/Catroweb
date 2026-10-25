package org.catrobat.web.android.graphics;

import org.teavm.jso.JSBody;

/** android.graphics.Paint for Catroid's text and bubble actors: Android's defaults, text in the browser's sans-serif. */
public class Paint {
	public enum Style {
		FILL, STROKE, FILL_AND_STROKE
	}

	public enum Align {
		LEFT, CENTER, RIGHT
	}

	int color = 0xff000000;
	float textSize = 12;
	float strokeWidth;
	Style style = Style.FILL;
	Align align = Align.LEFT;

	public void setColor(int color) {
		this.color = color;
	}

	public void setTextSize(float textSize) {
		this.textSize = textSize;
	}

	public void setStrokeWidth(float strokeWidth) {
		this.strokeWidth = strokeWidth;
	}

	public void setStyle(Style style) {
		this.style = style;
	}

	public void setTextAlign(Align align) {
		this.align = align;
	}

	public void setAntiAlias(boolean antiAlias) {
		// the browser always antialiases text and shapes
	}

	/** Negative, as on Android: the font's ascent above the baseline. */
	public float ascent() {
		return -(float) metric(font(), "", "fontBoundingBoxAscent", 0.93 * textSize);
	}

	public float descent() {
		return (float) metric(font(), "", "fontBoundingBoxDescent", 0.24 * textSize);
	}

	public float measureText(String text) {
		return (float) metric(font(), text, "width", 0);
	}

	/** The glyphs' box relative to the start of the baseline, as Android's getTextBounds. */
	public void getTextBounds(String text, int start, int end, Rect bounds) {
		String part = text.substring(start, end);
		bounds.left = (int) Math.floor(-metric(font(), part, "actualBoundingBoxLeft", 0));
		bounds.right = (int) Math.ceil(metric(font(), part, "actualBoundingBoxRight", measureText(part)));
		bounds.top = (int) Math.floor(-metric(font(), part, "actualBoundingBoxAscent", -ascent()));
		bounds.bottom = (int) Math.ceil(metric(font(), part, "actualBoundingBoxDescent", descent()));
	}

	String font() {
		return textSize + "px sans-serif";
	}

	String css() {
		return "rgba(" + (color >> 16 & 0xff) + "," + (color >> 8 & 0xff) + "," + (color & 0xff) + "," + ((color >>> 24) / 255.0) + ")";
	}

	@JSBody(params = {"font", "text", "name", "fallback"}, script = "var c = window.catrobatMeasure"
			+ " || (window.catrobatMeasure = document.createElement('canvas').getContext('2d'));"
			+ "c.font = font; var value = c.measureText(text)[name];"
			+ "return typeof value === 'number' ? value : fallback;")
	private static native double metric(String font, String text, String name, double fallback);
}
