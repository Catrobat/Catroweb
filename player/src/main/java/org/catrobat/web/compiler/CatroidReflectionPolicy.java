package org.catrobat.web.compiler;

import org.teavm.extension.Autoregistered;
import org.teavm.extension.introspect.IntrospectMember;
import org.teavm.extension.introspect.IntrospectMethod;
import org.teavm.extension.spi.reflection.SimpleReflectionPolicy;

/**
 * The project loader fills Catroid's model objects by field name, the way XStream does on Android. Only the model
 * packages: granting all of org.catrobat lets UI classes with stubbed dependencies reach TeaVM's code generator.
 */
@Autoregistered
public class CatroidReflectionPolicy extends SimpleReflectionPolicy {
	private static final String[] MODEL = {"org.catrobat.catroid.content.", "org.catrobat.catroid.formulaeditor.",
			"org.catrobat.catroid.common.", "org.catrobat.catroid.userbrick."};

	@Override
	protected void setup() {
		// Not selectClass(name): TeaVM also asks about classes it cannot resolve (null), which its named() rejects with an NPE.
		selectClasses(c -> null != c && isBuiltByXStream(c.name()))
				.foundByName()
				.reflectableMembers(XSTREAM_MEMBERS);
		// MapperWrapper asks getMethods() which wrapper overrides which method and calls that one directly: without
		// them, every lookup skips straight to DefaultMapper, past the aliases.
		selectClasses(c -> null != c && c.name().startsWith("com.thoughtworks.xstream.mapper."))
				.reflectableMethods(PUBLIC);
		// SerializationMembers takes its "no method" marker from this class's only declared method.
		selectClasses(c -> null != c && "com.thoughtworks.xstream.core.util.SerializationMembers$NO_METHOD_MARKER".equals(c.name()))
				.reflectableMembers(member -> true);
	}

	/**
	 * XStream needs fields and constructors only. Methods would make their signatures reflectable too, and
	 * readObject(ObjectInputStream) then hands TeaVM's code generator classes the browser lacks.
	 */
	private static final java.util.function.Predicate<IntrospectMember> XSTREAM_MEMBERS =
			member -> !(member instanceof IntrospectMethod method) || method.isConstructor();

	/** XStream.buildMapperDynamically and FieldDictionary: found by name, built through their constructor. */
	private static final java.util.Set<String> DYNAMIC_MAPPERS = java.util.Set.of("com.thoughtworks.xstream.mapper.EnumMapper",
			"com.thoughtworks.xstream.mapper.LambdaMapper", "com.thoughtworks.xstream.mapper.AnnotationMapper",
			"com.thoughtworks.xstream.converters.reflection.FieldUtil15");

	/** The sensors in a project's Lego NXT/EV3 settings. */
	private static final java.util.Set<String> LEGO_SENSORS = java.util.Set.of(
			"org.catrobat.catroid.devices.mindstorms.nxt.sensors.NXTSensor$Sensor",
			"org.catrobat.catroid.devices.mindstorms.ev3.sensors.EV3Sensor$Sensor");

	private static final java.util.Set<String> COLLECTIONS = java.util.Set.of("java.util.ArrayList", "java.util.LinkedList",
			"java.util.HashMap", "java.util.LinkedHashMap", "java.util.TreeMap", "java.util.HashSet", "java.util.LinkedHashSet",
			"java.util.TreeSet", "java.util.Vector", "java.util.concurrent.ConcurrentHashMap",
			"java.util.concurrent.CopyOnWriteArrayList");

	/**
	 * Catroid's model, and the collections, mappers and converters XStream builds by reflection. Not all of its
	 * converters: several reference classes the browser lacks, which TeaVM's code generator cannot handle.
	 */
	static boolean isBuiltByXStream(String name) {
		if (null == name) {
			return false;
		}
		if (DYNAMIC_MAPPERS.contains(name) || COLLECTIONS.contains(name) || LEGO_SENSORS.contains(name)) {
			return true;
		}
		// BrickSpinner: UI, not project data; TeaVM also writes broken metadata for its generic inner adapter class
		if (name.contains(".actions.") || name.contains(".brickspinner.BrickSpinner")) {
			return false;
		}
		for (String prefix : MODEL) {
			if (name.startsWith(prefix)) {
				return true;
			}
		}
		return false;
	}
}
