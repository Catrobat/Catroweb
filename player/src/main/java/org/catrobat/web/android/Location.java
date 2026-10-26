package org.catrobat.web.android;

/** android.location.Location for the browser (compat/Rewrite remaps it): a position from navigator.geolocation. */
public class Location {
	private final String provider;
	private double latitude;
	private double longitude;
	private double altitude;
	private boolean hasAltitude;
	private float accuracy;

	public Location(String provider) {
		this.provider = provider;
	}

	public String getProvider() {
		return provider;
	}

	public double getLatitude() {
		return latitude;
	}

	public void setLatitude(double latitude) {
		this.latitude = latitude;
	}

	public double getLongitude() {
		return longitude;
	}

	public void setLongitude(double longitude) {
		this.longitude = longitude;
	}

	public double getAltitude() {
		return altitude;
	}

	public void setAltitude(double altitude) {
		this.altitude = altitude;
		hasAltitude = true;
	}

	public boolean hasAltitude() {
		return hasAltitude;
	}

	public float getAccuracy() {
		return accuracy;
	}

	public void setAccuracy(float accuracy) {
		this.accuracy = accuracy;
	}
}
