package org.catrobat.web.compat;

import java.lang.reflect.Type;
import java.net.URL;
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
}
