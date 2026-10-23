package org.catrobat.web.android;

/** android.graphics.PointF for the browser (compat/Rewrite remaps it); the mockable stub keeps no values. */
public class PointF {
	public float x;
	public float y;

	public PointF() {
	}

	public PointF(float x, float y) {
		this.x = x;
		this.y = y;
	}

	public PointF(PointF p) {
		this(p.x, p.y);
	}

	public final void set(float x, float y) {
		this.x = x;
		this.y = y;
	}

	public final void set(PointF p) {
		set(p.x, p.y);
	}

	public final void negate() {
		set(-x, -y);
	}

	public final void offset(float dx, float dy) {
		set(x + dx, y + dy);
	}

	public final boolean equals(float x, float y) {
		return this.x == x && this.y == y;
	}

	public final float length() {
		return length(x, y);
	}

	public static float length(float x, float y) {
		return (float) Math.hypot(x, y);
	}

	@Override
	public boolean equals(Object o) {
		return o instanceof PointF p && x == p.x && y == p.y;
	}

	@Override
	public int hashCode() {
		return 31 * Float.floatToIntBits(x) + Float.floatToIntBits(y);
	}

	@Override
	public String toString() {
		return "PointF(" + x + ", " + y + ")";
	}
}
