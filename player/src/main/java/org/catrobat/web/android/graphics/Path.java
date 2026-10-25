package org.catrobat.web.android.graphics;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/** android.graphics.Path for the say bubble's arrow: a Path2D. */
public class Path {
	public enum FillType {
		WINDING, EVEN_ODD
	}

	final JSObject path = create();
	FillType fillType = FillType.WINDING;

	public void setFillType(FillType fillType) {
		this.fillType = fillType;
	}

	public void moveTo(float x, float y) {
		moveTo(path, x, y);
	}

	public void lineTo(float x, float y) {
		lineTo(path, x, y);
	}

	public void close() {
		close(path);
	}

	@JSBody(script = "return new Path2D();")
	private static native JSObject create();

	@JSBody(params = {"path", "x", "y"}, script = "path.moveTo(x, y);")
	private static native void moveTo(JSObject path, float x, float y);

	@JSBody(params = {"path", "x", "y"}, script = "path.lineTo(x, y);")
	private static native void lineTo(JSObject path, float x, float y);

	@JSBody(params = "path", script = "path.closePath();")
	private static native void close(JSObject path);
}
