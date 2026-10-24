package android.hardware;

/**
 * What WebContext hands Catroid for Context.SENSOR_SERVICE: a sensor manager without sensors. Catroid's SensorHandler
 * then treats acceleration, inclination and compass as unavailable and still answers the timer, touches and stage
 * size. In this package because SensorManager's constructor is package-private.
 */
public final class WebSensorManager extends SensorManager {
}
