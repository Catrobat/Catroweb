package org.catrobat.web.android;

import android.hardware.SensorEventListener;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

import java.util.ArrayList;
import java.util.List;

/**
 * android.hardware.SensorManager for the browser (compat/Rewrite remaps it), which WebContext hands Catroid's
 * SensorHandler. It has the two sensors SensorHandler prefers: linear acceleration from devicemotion and the rotation
 * vector from deviceorientation(absolute), so acceleration, inclination and compass take Catroid's own path from
 * sensor events to formula values. Desktop browsers send no such events, and the values stay 0. The static methods are
 * Android's matrix math (AOSP), which SensorHandler computes inclination and compass with.
 */
public class SensorManager {
	private static final int TYPE_LINEAR_ACCELERATION = 10;
	private static final int TYPE_ROTATION_VECTOR = 11;
	private final Sensor linearAcceleration = new Sensor(TYPE_LINEAR_ACCELERATION);
	private final Sensor rotationVector = new Sensor(TYPE_ROTATION_VECTOR);
	private final List<SensorEventListener> listeners = new ArrayList<>();
	private final List<Sensor> sensors = new ArrayList<>();
	private boolean listening;

	public Sensor getDefaultSensor(int type) {
		return type == TYPE_LINEAR_ACCELERATION ? linearAcceleration : type == TYPE_ROTATION_VECTOR ? rotationVector : null;
	}

	public boolean registerListener(SensorEventListener listener, Sensor sensor, int rate) {
		if (null == sensor) {
			return false;
		}
		listeners.add(listener);
		sensors.add(sensor);
		if (!listening) {
			listening = true;
			listen((type, x, y, z) -> {
				SensorEvent event = new SensorEvent();
				event.sensor = getDefaultSensor(type);
				event.values = new float[] {x, y, z};
				for (int i = 0; i < listeners.size(); i++) {
					if (sensors.get(i) == event.sensor) {
						listeners.get(i).onSensorChanged(event);
					}
				}
			});
		}
		return true;
	}

	public void unregisterListener(SensorEventListener listener) {
		for (int i = listeners.size() - 1; i >= 0; i--) {
			if (listeners.get(i) == listener) {
				listeners.remove(i);
				sensors.remove(i);
			}
		}
	}

	@JSFunctor
	interface Reading extends JSObject {
		void read(int type, float x, float y, float z);
	}

	/**
	 * The device frame of both events is Android's (x right, y up, z out of the screen, world x east, y north, z up).
	 * The rotation vector is the quaternion of the W3C's Z-X'-Y'' angles (its spec's formula), with w >= 0 as Android
	 * leaves it out. Absolute orientation (Android's Chrome, compass included) wins over relative orientation once it
	 * comes with angles; iPhones report alpha relative to where they started and the compass heading apart.
	 */
	@JSBody(params = "reading", script = "window.addEventListener('devicemotion', function (e) {"
			+ "  var a = e.acceleration; if (a && null != a.x) reading(10, a.x, a.y, a.z); });"
			+ "var absolute = false;"
			+ "var orientation = function (e) {"
			+ "  if (null == e.beta) return;"
			+ "  if (e.absolute) absolute = true; else if (absolute) return;"
			+ "  var alpha = null != e.webkitCompassHeading ? 360 - e.webkitCompassHeading : e.alpha || 0;"
			+ "  var h = Math.PI / 360, x = e.beta * h, y = e.gamma * h, z = alpha * h;"
			+ "  var cX = Math.cos(x), cY = Math.cos(y), cZ = Math.cos(z), sX = Math.sin(x), sY = Math.sin(y), sZ = Math.sin(z);"
			+ "  var s = cX * cY * cZ - sX * sY * sZ < 0 ? -1 : 1;"
			+ "  reading(11, s * (sX * cY * cZ - cX * sY * sZ), s * (cX * sY * cZ + sX * cY * sZ), s * (cX * cY * sZ + sX * sY * cZ));"
			+ "};"
			+ "window.addEventListener('deviceorientationabsolute', orientation);"
			+ "window.addEventListener('deviceorientation', orientation);")
	private static native void listen(Reading reading);

	public static void getRotationMatrixFromVector(float[] r, float[] rotationVector) {
		float q1 = rotationVector[0];
		float q2 = rotationVector[1];
		float q3 = rotationVector[2];
		float q0 = 1 - q1 * q1 - q2 * q2 - q3 * q3;
		q0 = q0 > 0 ? (float) Math.sqrt(q0) : 0;
		float sqQ1 = 2 * q1 * q1;
		float sqQ2 = 2 * q2 * q2;
		float sqQ3 = 2 * q3 * q3;
		float q1q2 = 2 * q1 * q2;
		float q3q0 = 2 * q3 * q0;
		float q1q3 = 2 * q1 * q3;
		float q2q0 = 2 * q2 * q0;
		float q2q3 = 2 * q2 * q3;
		float q1q0 = 2 * q1 * q0;
		int row = r.length == 16 ? 4 : 3;
		java.util.Arrays.fill(r, 0f);
		r[0] = 1 - sqQ2 - sqQ3;
		r[1] = q1q2 - q3q0;
		r[2] = q1q3 + q2q0;
		r[row] = q1q2 + q3q0;
		r[row + 1] = 1 - sqQ1 - sqQ3;
		r[row + 2] = q2q3 - q1q0;
		r[2 * row] = q1q3 - q2q0;
		r[2 * row + 1] = q2q3 + q1q0;
		r[2 * row + 2] = 1 - sqQ1 - sqQ2;
		if (row == 4) {
			r[15] = 1;
		}
	}

	public static float[] getOrientation(float[] r, float[] values) {
		int row = r.length == 16 ? 4 : 3;
		values[0] = (float) Math.atan2(r[1], r[row + 1]);
		values[1] = (float) Math.asin(-r[2 * row + 1]);
		values[2] = (float) Math.atan2(-r[2 * row], r[2 * row + 2]);
		return values;
	}

	public static boolean remapCoordinateSystem(float[] inR, int axisX, int axisY, float[] outR) {
		if (inR == outR || inR.length != outR.length || (axisX & 0x7C) != 0 || (axisY & 0x7C) != 0
				|| (axisX & 3) == 0 || (axisY & 3) == 0 || (axisX & 3) == (axisY & 3)) {
			return false; // Catroid never remaps in place nor onto the same axis
		}
		int axisZ = axisX ^ axisY;
		int x = (axisX & 3) - 1;
		int y = (axisY & 3) - 1;
		int z = (axisZ & 3) - 1;
		if (((x ^ (z + 1) % 3) | (y ^ (z + 2) % 3)) != 0) {
			axisZ ^= 0x80;
		}
		boolean sx = axisX >= 0x80;
		boolean sy = axisY >= 0x80;
		boolean sz = axisZ >= 0x80;
		int row = outR.length == 16 ? 4 : 3;
		for (int j = 0; j < 3; j++) {
			int offset = j * row;
			for (int i = 0; i < 3; i++) {
				if (x == i) {
					outR[offset + i] = sx ? -inR[offset] : inR[offset];
				}
				if (y == i) {
					outR[offset + i] = sy ? -inR[offset + 1] : inR[offset + 1];
				}
				if (z == i) {
					outR[offset + i] = sz ? -inR[offset + 2] : inR[offset + 2];
				}
			}
		}
		if (row == 4) {
			outR[3] = outR[7] = outR[11] = outR[12] = outR[13] = outR[14] = 0;
			outR[15] = 1;
		}
		return true;
	}

	/** For SensorHandler's accelerometer and magnetometer fallback, which the rotation vector above makes unused. */
	public static boolean getRotationMatrix(float[] r, float[] inclination, float[] gravity, float[] geomagnetic) {
		return false;
	}
}
