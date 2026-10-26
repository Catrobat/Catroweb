package org.catrobat.web.android;

/** android.hardware.SensorEvent for the browser (compat/Rewrite remaps it), whose values the SDK's makes final. */
public class SensorEvent {
	public Sensor sensor;
	public float[] values;
	public int accuracy;
	public long timestamp;
}
