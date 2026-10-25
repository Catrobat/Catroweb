package org.catrobat.web.compat;

import com.badlogic.gdx.graphics.Pixmap;
import com.badlogic.gdx.math.Polygon;
import com.thoughtworks.xstream.converters.Converter;
import com.thoughtworks.xstream.converters.SingleValueConverter;
import com.thoughtworks.xstream.converters.basic.StringBuilderConverter;
import com.thoughtworks.xstream.converters.basic.UUIDConverter;
import com.thoughtworks.xstream.converters.enums.EnumConverter;
import com.thoughtworks.xstream.converters.enums.EnumMapConverter;
import com.thoughtworks.xstream.converters.enums.EnumSetConverter;
import com.thoughtworks.xstream.converters.extended.AtomicBooleanConverter;
import com.thoughtworks.xstream.converters.extended.AtomicIntegerConverter;
import com.thoughtworks.xstream.converters.extended.AtomicLongConverter;
import com.thoughtworks.xstream.converters.extended.AtomicReferenceConverter;
import com.thoughtworks.xstream.core.util.SerializationMembers;
import com.thoughtworks.xstream.mapper.Mapper;
import org.catrobat.catroid.common.LookData;
import org.catrobat.catroid.sensing.CollisionInformation;
import org.catrobat.catroid.stage.OnSpeechRecognitionResultCallback;
import org.catrobat.catroid.stage.SpeechRecognitionHolderFactory;
import org.catrobat.catroid.stage.SpeechRecognitionHolderInterface;
import org.catrobat.catroid.stage.StageActivity;
import org.catrobat.catroid.stage.StageResourceHolder;
import org.catrobat.catroid.stage.TestResult;
import org.teavm.jso.JSBody;
import org.teavm.jso.browser.Window;
import org.teavm.jso.dom.html.HTMLElement;

import java.lang.reflect.Field;
import java.lang.reflect.Type;
import java.net.URL;
import java.util.Arrays;
import java.util.Collections;
import java.util.Enumeration;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

/** Targets of compat/Rewrite's redirects: browser stand-ins for JVM methods TeaVM lacks. Receiver comes first. */
public final class WebCompat {
	private WebCompat() {
	}

	public static android.content.res.Resources systemResources() {
		return org.catrobat.web.android.WebResources.INSTANCE;
	}

	public static android.content.Context appContext() {
		return org.catrobat.web.android.WebContext.INSTANCE;
	}

	/** There is one class loader in the browser; TeaVM resolves only the one-argument form for its reflection policy. */
	public static Class<?> forName(String name, boolean initialize, ClassLoader loader) throws ClassNotFoundException {
		return Class.forName(name);
	}

	/** XStream aliases JDK classes the browser lacks (java.time, java.sql, ...): JVM.loadClassForName gives null. */
	public static void alias(com.thoughtworks.xstream.XStream xstream, String name, Class<?> type) {
		if (null != type) {
			xstream.alias(name, type);
		}
	}

	public static void aliasType(com.thoughtworks.xstream.XStream xstream, String name, Class<?> type) {
		if (null != type) {
			xstream.aliasType(name, type);
		}
	}

	/** Same for its security setup: a permission for a missing (null) class fails every later check. */
	public static void allowTypeHierarchy(com.thoughtworks.xstream.XStream xstream, Class<?> type) {
		if (null != type) {
			xstream.allowTypeHierarchy(type);
		}
	}

	public static void allowTypes(com.thoughtworks.xstream.XStream xstream, Class<?>[] types) {
		xstream.allowTypes(Arrays.stream(types).filter(java.util.Objects::nonNull).toArray(Class<?>[]::new));
	}

	/**
	 * XStream builds these converters by class name and reflection. The others it registers that way are left out: they
	 * are for JDK types the browser lacks (java.time, javax.security, ...) or that Catroid projects never contain
	 * (Currency, Charset, Pattern, Throwable, Optional).
	 */
	public static void registerConverterDynamically(com.thoughtworks.xstream.XStream xstream, String className, int priority,
			Class<?>[] types, Object[] values) {
		Mapper mapper = null != values && values.length > 0 && values[0] instanceof Mapper given ? given : null;
		Object converter = switch (className) {
			case "com.thoughtworks.xstream.converters.enums.EnumConverter" -> new EnumConverter();
			case "com.thoughtworks.xstream.converters.enums.EnumSetConverter" -> new EnumSetConverter(mapper);
			case "com.thoughtworks.xstream.converters.enums.EnumMapConverter" -> new EnumMapConverter(mapper);
			case "com.thoughtworks.xstream.converters.extended.AtomicReferenceConverter" -> new AtomicReferenceConverter(mapper);
			case "com.thoughtworks.xstream.converters.extended.AtomicBooleanConverter" -> new AtomicBooleanConverter();
			case "com.thoughtworks.xstream.converters.extended.AtomicIntegerConverter" -> new AtomicIntegerConverter();
			case "com.thoughtworks.xstream.converters.extended.AtomicLongConverter" -> new AtomicLongConverter();
			case "com.thoughtworks.xstream.converters.basic.StringBuilderConverter" -> new StringBuilderConverter();
			case "com.thoughtworks.xstream.converters.basic.UUIDConverter" -> new UUIDConverter();
			default -> null;
		};
		if (converter instanceof Converter full) {
			xstream.registerConverter(full, priority);
		} else if (converter instanceof SingleValueConverter single) {
			xstream.registerConverter(single, priority);
		}
	}

	public static void setContextClassLoader(Thread thread, ClassLoader loader) {
		// There is one class loader in the browser.
	}

	public static Enumeration<URL> getResources(ClassLoader loader, String name) {
		return Collections.emptyEnumeration(); // no ServiceLoader providers: coroutines falls back to its defaults
	}

	public static Class<?> loadClass(ClassLoader loader, String name) throws ClassNotFoundException {
		return Class.forName(name);
	}

	public static <K, V> Enumeration<K> keys(ConcurrentHashMap<K, V> map) {
		return Collections.enumeration(((java.util.Map<K, V>) map).keySet()); // not KeySetView: TeaVM lacks it
	}

	/** Same bytes in, same UUID out as the JVM: a version 3 (MD5) UUID. */
	public static UUID nameUUIDFromBytes(byte[] name) {
		byte[] md5 = Md5.digest(name);
		md5[6] &= 0x0f;
		md5[6] |= 0x30;
		md5[8] &= 0x3f;
		md5[8] |= (byte) 0x80;
		StringBuilder hex = new StringBuilder(36);
		for (int i = 0; i < 16; i++) {
			if (4 == i || 6 == i || 8 == i || 10 == i) {
				hex.append('-');
			}
			hex.append(Character.forDigit((md5[i] >> 4) & 0xf, 16)).append(Character.forDigit(md5[i] & 0xf, 16));
		}
		return UUID.fromString(hex.toString()); // TeaVM has no UUID(long, long)
	}

	/** Catroid hashes images to cache their physics outlines and texts for speech; a real MD5, lower-case hex. */
	public static String md5Checksum(java.io.File file) {
		com.badlogic.gdx.files.FileHandle handle = absolute(com.badlogic.gdx.Gdx.files, file.getAbsolutePath());
		return handle.exists() ? md5Checksum(handle.readBytes()) : null;
	}

	public static String md5Checksum(String string) {
		return md5Checksum(string.getBytes(java.nio.charset.StandardCharsets.UTF_8));
	}

	private static String md5Checksum(byte[] bytes) {
		StringBuilder hex = new StringBuilder(32);
		for (byte b : Md5.digest(bytes)) {
			hex.append(Character.forDigit((b >> 4) & 0xf, 16)).append(Character.forDigit(b & 0xf, 16));
		}
		return hex.toString();
	}

	/**
	 * The app starts Koin; the player does not. Scene changes look up the speech recognition factory only to stop it,
	 * which noSpeechRecognition answers; anything else is a feature the player lacks (speech output and input).
	 */
	public static Object koinGet(Class<?> type) {
		if (SpeechRecognitionHolderFactory.class == type) {
			return null;
		}
		throw new UnsupportedOperationException("Not available on the web: " + type.getName());
	}

	/** Scene changes stop speech recognition, which the player does not offer. */
	public static SpeechRecognitionHolderInterface noSpeechRecognition(SpeechRecognitionHolderFactory factory) {
		return NO_SPEECH;
	}

	private static final SpeechRecognitionHolderInterface NO_SPEECH = new SpeechRecognitionHolderInterface() {
		@Override
		public void forceSetLanguage() {
		}

		@Override
		public void initSpeechRecognition(StageActivity stageActivity, StageResourceHolder stageResourceHolder) {
		}

		@Override
		public void startListening() {
		}

		@Override
		public void destroy() {
		}

		@Override
		public OnSpeechRecognitionResultCallback getCallback() {
			return null;
		}

		@Override
		public void setCallback(OnSpeechRecognitionResultCallback callback) {
		}
	};

	/**
	 * Catroid hands file work to Dispatchers.IO and comes back on Dispatchers.Main. The browser has one thread and no
	 * blocking, so both run the coroutine in place (the IO pool's workers would park, which the web cannot).
	 */
	public static kotlinx.coroutines.CoroutineDispatcher inPlaceDispatcher() {
		return kotlinx.coroutines.Dispatchers.getUnconfined();
	}

	public static kotlinx.coroutines.MainCoroutineDispatcher mainDispatcher() {
		return MAIN;
	}

	/** Android's main dispatcher comes from kotlinx-coroutines-android; the browser's main thread is the only one. */
	private static final kotlinx.coroutines.MainCoroutineDispatcher MAIN = new kotlinx.coroutines.MainCoroutineDispatcher() {
		@Override
		public kotlinx.coroutines.MainCoroutineDispatcher getImmediate() {
			return this;
		}

		@Override
		public boolean isDispatchNeeded(kotlin.coroutines.CoroutineContext context) {
			return false;
		}

		@Override
		public void dispatch(kotlin.coroutines.CoroutineContext context, Runnable block) {
			block.run();
		}
	};

	private static final java.util.Map<String, java.util.Map<Object, Object>> DEVICE_DATA = new java.util.HashMap<>();

	/**
	 * The "on device" variables and lists: Android keeps them as JSON through Gson, whose TypeToken needs generic
	 * signatures TeaVM drops. Here they stay in memory for this play, one map per accessor (variables, lists), as
	 * the project has one file each. ponytail: per play; localStorage when projects should remember across plays.
	 */
	public static java.util.Map<Object, Object> readDeviceData(org.catrobat.catroid.io.DeviceUserDataAccessor accessor) {
		java.util.Map<Object, Object> stored = DEVICE_DATA.get(accessor.getClass().getName());
		return null == stored ? null : copy(stored);
	}

	public static void writeDeviceData(org.catrobat.catroid.io.DeviceUserDataAccessor accessor, java.util.Map<Object, Object> map) {
		DEVICE_DATA.put(accessor.getClass().getName(), copy(map));
	}

	/** Like the JSON round trip, later changes to a list do not reach what was saved. */
	private static java.util.Map<Object, Object> copy(java.util.Map<Object, Object> map) {
		java.util.Map<Object, Object> copy = new java.util.HashMap<>();
		for (java.util.Map.Entry<Object, Object> entry : map.entrySet()) {
			Object value = entry.getValue();
			copy.put(entry.getKey(), value instanceof java.util.List<?> list ? new java.util.ArrayList<>(list) : value);
		}
		return copy;
	}

	/**
	 * The CSV brick's separator: what Catroid finds with ^(?:".*?"|[^,;]*?)([,;]), without the regex, whose lazy
	 * quantifiers overflow TeaVM's recursive matcher on a long CSV. A quoted first field ends at the first quote
	 * followed by a separator on its line; otherwise the first separator counts. Comma when there is none.
	 */
	public static char csvSeparator(org.catrobat.catroid.content.actions.StoreCSVIntoUserListAction action, String csv) {
		if (csv.startsWith("\"")) {
			for (int i = 1; i + 1 < csv.length() && !isLineTerminator(csv.charAt(i)); i++) {
				if ('"' == csv.charAt(i) && (',' == csv.charAt(i + 1) || ';' == csv.charAt(i + 1))) {
					return csv.charAt(i + 1);
				}
			}
		}
		for (int i = 0; i < csv.length(); i++) {
			if (',' == csv.charAt(i) || ';' == csv.charAt(i)) {
				return csv.charAt(i);
			}
		}
		return ',';
	}

	/** What the regex dot does not match. */
	private static boolean isLineTerminator(char c) {
		return '\n' == c || '\r' == c || '\u0085' == c || '\u2028' == c || '\u2029' == c;
	}

	/** Rounds to float precision, which TeaVM leaves out (compat/Rewrite inserts the calls). */
	@JSBody(params = "x", script = "return Math.fround(x);")
	public static native float fround(float x);

	/**
	 * LookData.getPixmap gives a look whose image is missing a 1x1 transparent pixmap when gdx reports "Couldn't load
	 * file:", Android's wording; gdx-teavm words it differently, so the look got none and drawing it crashed. Only a
	 * missing file, as there: an image that does not decode stays null and the look keeps its costume.
	 */
	public static Pixmap lookPixmap(LookData look) {
		Pixmap pixmap = look.getPixmap();
		if (null == pixmap && null != look.getFile() && !com.badlogic.gdx.Gdx.files.local(look.getFile().getAbsolutePath()).exists()) {
			pixmap = new Pixmap(1, 1, Pixmap.Format.Alpha);
			look.setPixmap(pixmap);
		}
		return pixmap;
	}

	/** ShowTextActor's upload of its text into the texture it bound (GLUtils.texImage2D on Android). */
	public static void texImage2D(int target, int level, org.catrobat.web.android.graphics.Bitmap bitmap, int border) {
		java.nio.ByteBuffer pixels = com.badlogic.gdx.utils.BufferUtils.newByteBuffer(bitmap.getWidth() * bitmap.getHeight() * 4);
		pixels.put(bitmap.rgba()).flip();
		com.badlogic.gdx.Gdx.gl.glTexImage2D(target, level, com.badlogic.gdx.graphics.GL20.GL_RGBA, bitmap.getWidth(),
				bitmap.getHeight(), border, com.badlogic.gdx.graphics.GL20.GL_RGBA, com.badlogic.gdx.graphics.GL20.GL_UNSIGNED_BYTE,
				pixels);
	}

	/** Android's GLES20 is the mockable no-op; the stage's GL is gdx's. */
	public static void glBindTexture(int target, int texture) {
		com.badlogic.gdx.Gdx.gl.glBindTexture(target, texture);
	}

	/**
	 * Where say and think bubbles attach: CollisionInformation.calculateBubblePositions, on the look's Pixmap instead
	 * of Android's decoded bitmap: in the first row from the top with an opaque pixel, its rightmost and leftmost ones
	 * (x > 0, as there), relative to the image centre. Opaque means alpha above 0 (see outline).
	 */
	public static void calculateBubblePositions(CollisionInformation information) {
		try {
			Field lookDataField = CollisionInformation.class.getDeclaredField("lookData");
			lookDataField.setAccessible(true);
			Pixmap pixmap = lookPixmap((LookData) lookDataField.get(information));
			int width = pixmap.getWidth();
			int height = pixmap.getHeight();
			androidx.core.util.Pair<Integer, Integer> right = null;
			androidx.core.util.Pair<Integer, Integer> left = null;
			for (int y = 0; y < height && null == right; y++) {
				for (int x = width - 1; x > 0; x--) {
					if (0 != (pixmap.getPixel(x, y) & 0xff)) {
						androidx.core.util.Pair<Integer, Integer> position = new androidx.core.util.Pair<>(x - width / 2, height / 2 - y);
						if (null == right) {
							right = position;
						}
						left = position;
					}
				}
			}
			Field rightField = CollisionInformation.class.getDeclaredField("rightBubblePos");
			Field leftField = CollisionInformation.class.getDeclaredField("leftBubblePos");
			rightField.setAccessible(true);
			leftField.setAccessible(true);
			rightField.set(information, right);
			leftField.set(information, left);
		} catch (ReflectiveOperationException e) {
			throw new IllegalStateException("Catroid's CollisionInformation changed", e);
		}
	}

	/** Koin 2.1 was compiled against Kotlin 1.3's double-valued TimeMark; it only measures for debug logs. */
	public static double elapsedNow(kotlin.time.TimeMark timeMark) {
		return 0.0;
	}

	/** Same Kotlin 1.3 mismatch for Duration.inMilliseconds. */
	public static double durationMillis(double duration) {
		return 0.0;
	}

	/** Gson's TypeToken subclasses ask for their type argument; the device-data maps only need a raw Map. */
	public static Type getGenericSuperclass(Class<?> type) {
		return type.getSuperclass();
	}

	/** Raw interfaces, like getGenericSuperclass: TeaVM keeps no generic signatures for them. */
	public static Type[] getGenericInterfaces(Class<?> type) {
		return type.getInterfaces();
	}

	/** XStream adds its version to conversion errors; there is no jar manifest in the browser. */
	public static String implementationVersion(Package pkg) {
		return null;
	}

	/** XStream's JVM.isAWTAvailable/isSwingAvailable/isSQLAvailable: none of these exist in the browser. */
	public static boolean unavailable() {
		return false;
	}

	/** No Java serialization in the browser, so no readObject/writeObject hooks: XStream reads the fields instead. */
	public static boolean noSerializationHook(SerializationMembers members, Class<?> type, boolean includeBaseClasses) {
		return false;
	}

	/** gdx-teavm has no absolute files; Catroid's looks and sounds live in its in-memory local storage instead. */
	public static com.badlogic.gdx.files.FileHandle absolute(com.badlogic.gdx.Files files, String path) {
		return files.local(path);
	}

	/** The finish-stage brick: on Android the stage activity closes; here the stage stops. */
	public static void finishStage() {
		StageActivity.stageListener.setPaused(true);
	}

	/**
	 * Catroid's test projects (catrobatTests) end with an assertion or a finish brick that reports here; the first
	 * result counts, as on Android where the activity is gone after it. The page keeps it for the conformance runner.
	 */
	public static void finishTestWithResult(TestResult result) {
		HTMLElement body = Window.current().getDocument().getBody();
		if (!body.hasAttribute("data-test-result")) {
			body.setAttribute("data-test-result", result.getResultCode() + " " + result.getMessage());
			System.out.println("player: test finished: " + result.getResultCode() + " " + result.getMessage());
			reportTestFinished();
		}
		finishStage();
	}

	/** Lets scripts/smoke_server.py stop waiting; elsewhere it is a request nobody answers. */
	@JSBody(script = "fetch('__test-finished').catch(function () {});")
	private static native void reportTestFinished();

	/**
	 * Catroid's collision outline of a look, as the app gets it: from the PNG's metadata (the app writes it there, so
	 * shared projects usually carry it), else computed from the pixels with Catroid's own steps, else the bounding
	 * box (what the app uses for images that are not PNG). The app reads the image with Android's BitmapFactory;
	 * here it is the look's Pixmap.
	 */
	public static void loadCollisionPolygon(CollisionInformation information) {
		LookData look;
		try {
			Field lookDataField = CollisionInformation.class.getDeclaredField("lookData");
			lookDataField.setAccessible(true);
			look = (LookData) lookDataField.get(information);
		} catch (ReflectiveOperationException e) {
			throw new IllegalStateException("Catroid's CollisionInformation changed", e);
		}
		Pixmap pixmap = look.getPixmap();
		Polygon[] polygons = new Polygon[0];
		if (isPng(look.getFile().getAbsolutePath())) {
			polygons = CollisionInformation.getCollisionPolygonFromPNGMeta(look.getFile().getAbsolutePath());
			if (0 == polygons.length) {
				polygons = outline(pixmap);
				if (null == polygons) { // nothing opaque: like the app, the look touches nothing
					information.collisionPolygons = new Polygon[0];
					return;
				}
			}
		}
		if (0 == polygons.length) {
			float width = pixmap.getWidth();
			float height = pixmap.getHeight();
			polygons = new Polygon[] {new Polygon(new float[] {0, 0, width, 0, width, height, 0, height})};
		}
		information.collisionPolygons = polygons;
	}

	/**
	 * CollisionInformation.createCollisionPolygon and createBoundingPolygonVertices, on a Pixmap: null when nothing
	 * is opaque, empty when the outline simplifies away (both as in the app).
	 */
	private static Polygon[] outline(Pixmap pixmap) {
		int width = pixmap.getWidth();
		int height = pixmap.getHeight();
		boolean[][] grid = new boolean[width][height];
		for (int x = 0; x < width; x++) {
			for (int y = 0; y < height; y++) {
				// Flipped like the app's bitmap. Android premultiplies alpha, so its Color.TRANSPARENT test means alpha 0;
				// gdx keeps the colour of fully transparent pixels.
				grid[x][y] = 0 != (pixmap.getPixel(x, height - 1 - y) & 0xff);
			}
		}
		var vertical = CollisionInformation.createVerticalVertices(grid, width, height);
		var horizontal = CollisionInformation.createHorizontalVertices(grid, width, height);
		if (vertical.isEmpty() || horizontal.isEmpty()) {
			return null;
		}
		// ponytail: the joining below is quadratic in edge segments; the app computes it once and stores it in the PNG,
		// the player on every load. A look this ragged (noisy alpha) collides as its bounding box instead.
		if (vertical.size() + horizontal.size() > 20000) {
			return new Polygon[0];
		}
		java.util.List<java.util.ArrayList<org.catrobat.catroid.sensing.CollisionPolygonVertex>> outlines = new java.util.ArrayList<>();
		outlines.add(new java.util.ArrayList<>(java.util.List.of(vertical.remove(0))));
		do {
			var current = outlines.get(outlines.size() - 1);
			var end = current.get(current.size() - 1);
			boolean found = false;
			for (int h = 0; h < horizontal.size(); h++) {
				if (end.isConnected(horizontal.get(h))) {
					end = horizontal.remove(h);
					current.add(end);
					found = true;
					break;
				}
			}
			for (int v = 0; v < vertical.size(); v++) {
				if (end.isConnected(vertical.get(v))) {
					current.add(vertical.remove(v));
					found = true;
					break;
				}
			}
			if (!found) {
				outlines.add(new java.util.ArrayList<>(java.util.List.of(vertical.remove(0))));
			}
		} while (!horizontal.isEmpty());

		float epsilon = org.catrobat.catroid.common.Constants.COLLISION_POLYGON_CREATION_EPSILON;
		java.util.List<Polygon> polygons;
		do {
			polygons = new java.util.ArrayList<>();
			for (var outline : outlines) {
				var points = CollisionInformation.getPointsFromPolygonVertices(outline);
				var simplified = CollisionInformation.simplifyPolygon(points, 0, points.size() - 1, epsilon);
				float dx = simplified.get(simplified.size() - 1).x - simplified.get(0).x;
				float dy = simplified.get(simplified.size() - 1).y - simplified.get(0).y;
				if (Math.hypot(dx, dy) < epsilon) {
					simplified.remove(simplified.size() - 1);
				}
				if (simplified.size() >= 3) {
					polygons.add(CollisionInformation.createPolygonFromPoints(simplified));
				}
			}
			epsilon *= 1.2f;
		} while (vertices(polygons) > org.catrobat.catroid.common.Constants.COLLISION_VERTEX_LIMIT);
		return polygons.toArray(new Polygon[0]);
	}

	private static int vertices(java.util.List<Polygon> polygons) {
		int count = 0;
		for (Polygon polygon : polygons) {
			count += polygon.getVertices().length / 2;
		}
		return count;
	}

	private static boolean isPng(String path) {
		byte[] bytes = com.badlogic.gdx.Gdx.files.local(path).readBytes();
		return bytes.length > 8 && (bytes[0] & 0xff) == 0x89 && bytes[1] == 'P' && bytes[2] == 'N' && bytes[3] == 'G';
	}

	/**
	 * ImageEditing.readMetaDataStringFromPNG without pngj's file access: the text of the PNG's tEXt or (uncompressed)
	 * iTXt chunk with that keyword, else "".
	 */
	public static String readPngText(String path, String key) {
		byte[] png = com.badlogic.gdx.Gdx.files.local(path).readBytes();
		byte[] keyword = (key + "\0").getBytes(java.nio.charset.StandardCharsets.ISO_8859_1);
		for (int i = 8; i + 8 <= png.length; ) {
			int length = (png[i] & 0xff) << 24 | (png[i + 1] & 0xff) << 16 | (png[i + 2] & 0xff) << 8 | png[i + 3] & 0xff;
			String type = new String(png, i + 4, 4, java.nio.charset.StandardCharsets.ISO_8859_1);
			int data = i + 8;
			if (length < 0 || length > png.length - data) {
				break; // malformed: the app's pngj would fail, and the app then computes the outline
			}
			int end = data + length;
			if (("tEXt".equals(type) || "iTXt".equals(type)) && startsWith(png, data, length, keyword)) {
				int at = data + keyword.length;
				if ("tEXt".equals(type)) {
					return new String(png, at, end - at, java.nio.charset.StandardCharsets.ISO_8859_1);
				}
				if (at + 2 > end || 0 != png[at]) {
					return ""; // truncated, or compressed (the app never writes it so)
				}
				at += 2; // compression flag and method
				int zeros = 0;
				for (; zeros < 2 && at < end; at++) { // language tag and translated keyword
					if (0 == png[at]) {
						zeros++;
					}
				}
				return 2 == zeros ? new String(png, at, end - at, java.nio.charset.StandardCharsets.UTF_8) : "";
			}
			i = data + length + 4; // and the CRC
		}
		return "";
	}

	private static boolean startsWith(byte[] bytes, int offset, int length, byte[] prefix) {
		if (length < prefix.length) {
			return false;
		}
		for (int i = 0; i < prefix.length; i++) {
			if (bytes[offset + i] != prefix[i]) {
				return false;
			}
		}
		return true;
	}
}
