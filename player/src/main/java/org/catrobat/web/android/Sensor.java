package org.catrobat.web.android;

/** android.hardware.Sensor for the browser (compat/Rewrite remaps it): one of the two SensorManager offers. */
public class Sensor {
	private final int type;

	Sensor(int type) {
		this.type = type;
	}

	public int getType() {
		return type;
	}
}
