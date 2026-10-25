package org.catrobat.web.android.graphics;

/** android.graphics.Rect, as Paint.getTextBounds fills it. */
public class Rect {
	public int left;
	public int top;
	public int right;
	public int bottom;

	public int width() {
		return right - left;
	}

	public int height() {
		return bottom - top;
	}
}
