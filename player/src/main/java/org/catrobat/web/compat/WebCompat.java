package org.catrobat.web.compat;

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
	 * XStream builds these converters by class name and reflection; the others it registers that way are for JDK types
	 * the browser lacks (java.time, javax.security, ...) and are left out.
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
}
