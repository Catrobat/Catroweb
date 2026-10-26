package org.catrobat.web.android;

import android.location.GnssStatus;
import android.location.LocationListener;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

import java.util.HashMap;
import java.util.Map;

/**
 * android.location.LocationManager for the browser (compat/Rewrite remaps it), with the calls Catroid's SensorHandler
 * makes: navigator.geolocation is its one provider, "gps", so SensorHandler takes every position it reports. The
 * browser asks for permission when SensorHandler starts listening, which the player only lets it do for projects that
 * read the location.
 */
public class LocationManager {
	private static final String GPS_PROVIDER = "gps";
	private final Map<LocationListener, Integer> watches = new HashMap<>();

	public boolean isProviderEnabled(String provider) {
		return GPS_PROVIDER.equals(provider) && hasGeolocation();
	}

	public void requestLocationUpdates(String provider, long minTime, float minDistance, LocationListener listener) {
		removeUpdates(listener);
		watches.put(listener, watch((latitude, longitude, accuracy, altitude, hasAltitude) -> {
			Location location = new Location(GPS_PROVIDER);
			location.setLatitude(latitude);
			location.setLongitude(longitude);
			location.setAccuracy((float) accuracy);
			if (hasAltitude) {
				location.setAltitude(altitude);
			}
			listener.onLocationChanged(location);
		}));
	}

	public void removeUpdates(LocationListener listener) {
		Integer watch = watches.remove(listener);
		if (null != watch) {
			clearWatch(watch);
		}
	}

	/** The browser has no satellite status: SensorHandler then takes positions from any provider. */
	public boolean registerGnssStatusCallback(GnssStatus.Callback callback) {
		return false;
	}

	public boolean registerGnssStatusCallback(java.util.concurrent.Executor executor, GnssStatus.Callback callback) {
		return false;
	}

	public void unregisterGnssStatusCallback(GnssStatus.Callback callback) {
	}

	@JSFunctor
	interface Position extends JSObject {
		void update(double latitude, double longitude, double accuracy, double altitude, boolean hasAltitude);
	}

	@JSBody(script = "return !!navigator.geolocation;")
	private static native boolean hasGeolocation();

	@JSBody(params = "position", script = "return navigator.geolocation.watchPosition(function (p) {"
			+ "  var c = p.coords; position(c.latitude, c.longitude, c.accuracy, c.altitude || 0, null != c.altitude);"
			+ "}, function () {}, {enableHighAccuracy: true});")
	private static native int watch(Position position);

	@JSBody(params = "id", script = "navigator.geolocation.clearWatch(id);")
	private static native void clearWatch(int id);
}
