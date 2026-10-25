package org.catrobat.web.android.graphics;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/** android.graphics.Canvas on a Bitmap, with the drawing Catroid's text and bubble actors do: a 2D canvas context. */
public class Canvas {
	private final JSObject context;

	public Canvas(Bitmap bitmap) {
		context = context(bitmap.canvas);
	}

	public void drawText(String text, float x, float y, Paint paint) {
		text(context, text, x, y, paint.font(), paint.css(), paint.style.ordinal(), stroke(paint), paint.align.name().toLowerCase());
	}

	public void drawBitmap(Bitmap bitmap, float left, float top, Paint paint) {
		image(context, bitmap.canvas, left, top);
	}

	public void drawCircle(float x, float y, float radius, Paint paint) {
		circle(context, x, y, radius, paint.css(), paint.style.ordinal(), stroke(paint));
	}

	public void drawRoundRect(RectF rect, float rx, float ry, Paint paint) {
		roundRect(context, rect.left, rect.top, rect.right - rect.left, rect.bottom - rect.top, Math.min(rx, ry), paint.css(),
				paint.style.ordinal(), stroke(paint));
	}

	public void drawPath(Path path, Paint paint) {
		path(context, path.path, Path.FillType.EVEN_ODD == path.fillType, paint.css(), paint.style.ordinal(), stroke(paint));
	}

	/** Android draws a zero-width stroke one pixel wide. */
	private static float stroke(Paint paint) {
		return Math.max(1, paint.strokeWidth);
	}

	@JSBody(params = "canvas", script = "return canvas.getContext('2d');")
	private static native JSObject context(JSObject canvas);

	// style: 0 fill, 1 stroke, 2 fill and stroke (Paint.Style's order)
	@JSBody(params = {"c", "text", "x", "y", "font", "color", "style", "width", "align"}, script = "c.font = font;"
			+ "c.textAlign = align; c.textBaseline = 'alphabetic';"
			+ "c.fillStyle = color; c.strokeStyle = color; c.lineWidth = width;"
			+ "if (style !== 1) c.fillText(text, x, y);"
			+ "if (style !== 0) c.strokeText(text, x, y);")
	private static native void text(JSObject c, String text, float x, float y, String font, String color, int style, float width,
			String align);

	@JSBody(params = {"c", "image", "x", "y"}, script = "c.drawImage(image, x, y);")
	private static native void image(JSObject c, JSObject image, float x, float y);

	@JSBody(params = {"c", "x", "y", "r", "color", "style", "width"}, script = "c.beginPath();"
			+ "c.arc(x, y, Math.max(0, r), 0, 2 * Math.PI);"
			+ "c.fillStyle = color; c.strokeStyle = color; c.lineWidth = width;"
			+ "if (style !== 1) c.fill();"
			+ "if (style !== 0) c.stroke();")
	private static native void circle(JSObject c, float x, float y, float r, String color, int style, float width);

	@JSBody(params = {"c", "x", "y", "w", "h", "r", "color", "style", "width"}, script = "r = Math.max(0, Math.min(r, w / 2, h / 2));"
			+ "c.beginPath();"
			+ "c.moveTo(x + r, y); c.arcTo(x + w, y, x + w, y + h, r); c.arcTo(x + w, y + h, x, y + h, r);"
			+ "c.arcTo(x, y + h, x, y, r); c.arcTo(x, y, x + w, y, r); c.closePath();"
			+ "c.fillStyle = color; c.strokeStyle = color; c.lineWidth = width;"
			+ "if (style !== 1) c.fill();"
			+ "if (style !== 0) c.stroke();")
	private static native void roundRect(JSObject c, float x, float y, float w, float h, float r, String color, int style, float width);

	@JSBody(params = {"c", "path", "evenOdd", "color", "style", "width"}, script = "c.fillStyle = color; c.strokeStyle = color;"
			+ "c.lineWidth = width;"
			+ "if (style !== 1) c.fill(path, evenOdd ? 'evenodd' : 'nonzero');"
			+ "if (style !== 0) c.stroke(path);")
	private static native void path(JSObject c, JSObject path, boolean evenOdd, String color, int style, float width);
}
