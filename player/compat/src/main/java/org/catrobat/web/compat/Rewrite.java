package org.catrobat.web.compat;

import org.objectweb.asm.ClassReader;
import org.objectweb.asm.ClassWriter;
import org.objectweb.asm.Opcodes;
import org.objectweb.asm.Type;
import org.objectweb.asm.commons.ClassRemapper;
import org.objectweb.asm.commons.SimpleRemapper;
import org.objectweb.asm.tree.AbstractInsnNode;
import org.objectweb.asm.tree.ClassNode;
import org.objectweb.asm.tree.FieldInsnNode;
import org.objectweb.asm.tree.InsnList;
import org.objectweb.asm.tree.InsnNode;
import org.objectweb.asm.tree.LdcInsnNode;
import org.objectweb.asm.tree.MethodInsnNode;
import org.objectweb.asm.tree.MethodNode;
import org.objectweb.asm.tree.TypeInsnNode;
import org.objectweb.asm.tree.VarInsnNode;

import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.PrintStream;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.Enumeration;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;
import java.util.zip.ZipOutputStream;

/**
 * Makes Catroid's compiled engine and its libraries compile under TeaVM without touching their sources:
 * <ol>
 * <li>remaps the Android libGDX backend classes onto web stand-ins,</li>
 * <li>redirects JVM methods TeaVM lacks onto {@link WebCompat},</li>
 * <li>replaces the body of any method that still uses something missing on the web (the list TeaVM itself
 * reports) with a "not available on the web" exception.</li>
 * </ol>
 * Usage: Rewrite missing.txt in.jar out.jar
 */
public final class Rewrite {
	private static final String COMPAT = "org/catrobat/web/compat/WebCompat";

	static final Map<String, String> REMAP = Map.ofEntries(
			Map.entry("android/os/SystemClock", "org/catrobat/web/android/SystemClock"),
			Map.entry("android/util/Log", "org/catrobat/web/android/Log"),
			Map.entry("android/graphics/PointF", "org/catrobat/web/android/PointF"),
			Map.entry("android/util/SparseIntArray", "org/catrobat/web/android/SparseIntArray"),
			Map.entry("android/util/SparseArray", "org/catrobat/web/android/SparseArray"),
			Map.entry("java/util/concurrent/locks/Lock", "org/catrobat/web/java/Lock"),
			Map.entry("java/util/concurrent/locks/ReentrantLock", "org/catrobat/web/java/ReentrantLock"),
			Map.entry("java/io/ObjectStreamField", "org/catrobat/web/java/ObjectStreamField"),
			Map.entry("android/media/MediaPlayer", "org/catrobat/web/android/MediaPlayer"),
			Map.entry("android/media/MediaMetadataRetriever", "org/catrobat/web/android/MediaMetadataRetriever"),
			Map.entry("org/catrobat/catroid/web/WebConnectionHolder", "org/catrobat/web/catroid/WebConnectionHolder"),
			Map.entry("com/badlogic/gdx/backends/android/AndroidApplication", "org/catrobat/web/compat/WebAndroidApplication"),
			Map.entry("com/badlogic/gdx/backends/android/AndroidGraphics", "org/catrobat/web/compat/WebAndroidGraphics"),
			Map.entry("com/badlogic/gdx/backends/android/AndroidApplicationConfiguration", "org/catrobat/web/compat/WebAndroidApplicationConfiguration"));

	/** owner.name+desc of instance or static JVM methods TeaVM lacks -> WebCompat method (receiver first). */
	static final Map<String, String> REDIRECT = Map.ofEntries(
			Map.entry("java/lang/Thread.setContextClassLoader(Ljava/lang/ClassLoader;)V", "setContextClassLoader"),
			Map.entry("java/lang/ClassLoader.getResources(Ljava/lang/String;)Ljava/util/Enumeration;", "getResources"),
			Map.entry("java/lang/ClassLoader.loadClass(Ljava/lang/String;)Ljava/lang/Class;", "loadClass"),
			Map.entry("java/util/concurrent/ConcurrentHashMap.keys()Ljava/util/Enumeration;", "keys"),
			Map.entry("org/catrobat/catroid/content/bricks/ConcurrentFormulaHashMap.keys()Ljava/util/Enumeration;", "keys"),
			Map.entry("java/util/UUID.nameUUIDFromBytes([B)Ljava/util/UUID;", "nameUUIDFromBytes"),
			Map.entry("org/catrobat/catroid/CatroidApplication.getAppContext()Landroid/content/Context;", "appContext"),
			Map.entry("android/content/res/Resources.getSystem()Landroid/content/res/Resources;", "systemResources"),
			Map.entry("kotlin/time/TimeMark.elapsedNow()D", "elapsedNow"),
			Map.entry("kotlin/time/Duration.getInMilliseconds-impl(D)D", "durationMillis"),
			Map.entry("java/lang/Class.forName(Ljava/lang/String;ZLjava/lang/ClassLoader;)Ljava/lang/Class;", "forName"),
			Map.entry("java/lang/Class.getGenericSuperclass()Ljava/lang/reflect/Type;", "getGenericSuperclass"),
			Map.entry("java/lang/Class.getGenericInterfaces()[Ljava/lang/reflect/Type;", "getGenericInterfaces"),
			Map.entry("java/lang/Package.getImplementationVersion()Ljava/lang/String;", "implementationVersion"),
			Map.entry("com/badlogic/gdx/Files.absolute(Ljava/lang/String;)Lcom/badlogic/gdx/files/FileHandle;", "absolute"),
			Map.entry("org/catrobat/catroid/stage/StageActivity.finishStage()V", "finishStage"),
			Map.entry("org/catrobat/catroid/stage/StageActivity.finishTestWithResult(Lorg/catrobat/catroid/stage/TestResult;)V",
					"finishTestWithResult"),
			Map.entry("org/catrobat/catroid/sensing/CollisionInformation.loadCollisionPolygon()V", "loadCollisionPolygon"),
			Map.entry("com/thoughtworks/xstream/core/util/SerializationMembers.supportsReadObject(Ljava/lang/Class;Z)Z", "noSerializationHook"),
			Map.entry("com/thoughtworks/xstream/core/util/SerializationMembers.supportsWriteObject(Ljava/lang/Class;Z)Z", "noSerializationHook"),
			Map.entry("com/thoughtworks/xstream/core/JVM.isAWTAvailable()Z", "unavailable"),
			Map.entry("com/thoughtworks/xstream/core/JVM.isSwingAvailable()Z", "unavailable"),
			Map.entry("com/thoughtworks/xstream/core/JVM.isSQLAvailable()Z", "unavailable"),
			Map.entry("com/thoughtworks/xstream/XStream.alias(Ljava/lang/String;Ljava/lang/Class;)V", "alias"),
			Map.entry("com/thoughtworks/xstream/XStream.aliasType(Ljava/lang/String;Ljava/lang/Class;)V", "aliasType"),
			Map.entry("com/thoughtworks/xstream/XStream.allowTypeHierarchy(Ljava/lang/Class;)V", "allowTypeHierarchy"),
			Map.entry("com/thoughtworks/xstream/XStream.allowTypes([Ljava/lang/Class;)V", "allowTypes"),
			Map.entry("com/thoughtworks/xstream/XStream.registerConverterDynamically(Ljava/lang/String;I[Ljava/lang/Class;[Ljava/lang/Object;)V",
					"registerConverterDynamically"));

	static PrintStream report;

	public static void main(String[] args) throws Exception {
		if (null != System.getenv("STUB_REPORT")) {
			report = new PrintStream(new FileOutputStream(System.getenv("STUB_REPORT"), true), true);
		}
		Set<String> missing = new HashSet<>();
		for (String line : Files.readAllLines(Path.of(args[0]))) {
			String symbol = symbol(line);
			if (null != symbol) {
				missing.add(symbol);
			}
		}

		int[] stats = new int[2];
		try (ZipFile in = new ZipFile(args[1]); ZipOutputStream out = new ZipOutputStream(Files.newOutputStream(Path.of(args[2])))) {
			Set<String> noArgConstructors = noArgConstructors(in);
			for (Enumeration<? extends ZipEntry> e = in.entries(); e.hasMoreElements(); ) {
				ZipEntry entry = e.nextElement();
				if (entry.isDirectory()) {
					continue;
				}
				if (REMAP.containsKey(entry.getName().replace(".class", ""))) {
					continue; // replaced by its web version
				}
				byte[] bytes;
				try (InputStream s = in.getInputStream(entry)) {
					bytes = s.readAllBytes();
				}
				if (entry.getName().endsWith(".class") && !entry.getName().startsWith("META-INF/")) {
					bytes = rewrite(bytes, missing, noArgConstructors, stats);
				}
				out.putNextEntry(new ZipEntry(entry.getName()));
				out.write(bytes);
				out.closeEntry();
			}
		}
		System.out.println(Path.of(args[1]).getFileName() + ": " + stats[0] + " calls redirected, " + stats[1] + " methods stubbed");
	}

	/** "Method a.b.C.m(I)V was not found" -> "a/b/C.m(I)V"; classes and fields -> "a/b/C" / "a/b/C.f". */
	static String symbol(String raw) {
		String line = raw;
		if (line.contains(", which is missing in the classpath")) {
			String[] words = line.split(" ");
			return words[words.length - 7].replace(",", "").replace('.', '/'); // "... implements a.B, which is missing in the classpath"
		}
		if (line.startsWith("Native method ") && line.contains(" has no implementation")) {
			line = "Method " + line.substring("Native method ".length(), line.indexOf(" has no implementation")) + " was not found";
		}
		String[] parts = line.trim().split(" ");
		if (parts.length < 2 || !line.contains("was not found")) {
			return null;
		}
		String s = parts[1];
		if ("Class".equals(parts[0])) {
			return s.replace('.', '/');
		}
		int paren = s.indexOf('(');
		String head = paren < 0 ? s : s.substring(0, paren);
		int dot = head.lastIndexOf('.');
		return head.substring(0, dot).replace('.', '/') + "." + head.substring(dot + 1) + (paren < 0 ? "" : s.substring(paren));
	}

	static byte[] rewrite(byte[] bytes, Set<String> missing, Set<String> noArgConstructors, int[] stats) {
		ClassNode node = new ClassNode();
		new ClassReader(bytes).accept(new ClassRemapper(node, new SimpleRemapper(Opcodes.ASM9, REMAP)), 0);
		boolean changed = mentionsRemapped(bytes) | addNoArgConstructor(node, noArgConstructors);
		if (node.interfaces.removeIf(missing::contains)) {
			changed = true; // the class works without claiming a type the browser lacks
		}
		if (null != node.superName && missing.contains(node.superName)) {
			node.superName = "java/lang/Object";
			changed = true;
		}
		for (MethodNode method : node.methods) {
			String reason = null; // why the method is stubbed: the first missing thing it uses
			if (0 != (method.access & Opcodes.ACC_NATIVE) && missing.contains(node.name + "." + method.name + method.desc)) {
				method.access &= ~Opcodes.ACC_NATIVE; // JNI code has no browser counterpart
				reason = "native";
			}
			for (AbstractInsnNode insn = method.instructions.getFirst(); null != insn; insn = insn.getNext()) {
				if (insn instanceof MethodInsnNode call && isClassLookup(call) && previousInstruction(call) instanceof LdcInsnNode name
						&& name.cst instanceof String className) {
					// Java 1.4-style Foo.class: ldc "a.b.Foo"; invokestatic class$. TeaVM resolves names only for
					// classes a reflection policy lists, so turn it back into a real class literal.
					name.cst = className.startsWith("[") ? Type.getType(className.replace('.', '/')) : Type.getObjectType(className.replace('.', '/'));
					node.version = Math.max(node.version, Opcodes.V1_5); // ldc of a class needs class file version 49
					method.instructions.remove(call);
					insn = name;
					changed = true;
					if (null == reason) {
						reason = missingUse(name, missing); // now a Foo.class constant like any other
					}
					continue;
				}
				if (insn instanceof MethodInsnNode call) {
					String target = REDIRECT.get(call.owner + "." + call.name + call.desc);
					if (null == target && "setContextClassLoader".equals(call.name) && "(Ljava/lang/ClassLoader;)V".equals(call.desc)) {
						target = "setContextClassLoader"; // also on Thread subclasses
					}
					if (null != target) {
						if (call.getOpcode() != Opcodes.INVOKESTATIC) {
							call.desc = "(L" + receiver(call.owner, target) + ";" + call.desc.substring(1);
						}
						call.setOpcode(Opcodes.INVOKESTATIC);
						call.owner = COMPAT;
						call.name = target;
						call.itf = false;
						stats[0]++;
						changed = true;
						continue;
					}
				}
				if (null == reason) {
					reason = missingUse(insn, missing);
				}
			}
			if (null != reason && 0 == (method.access & Opcodes.ACC_ABSTRACT)) {
				InsnList body = new InsnList();
				body.add(new TypeInsnNode(Opcodes.NEW, "java/lang/UnsupportedOperationException"));
				body.add(new InsnNode(Opcodes.DUP));
				body.add(new LdcInsnNode("Not available on the web: " + node.name + "." + method.name));
				body.add(new MethodInsnNode(Opcodes.INVOKESPECIAL, "java/lang/UnsupportedOperationException", "<init>", "(Ljava/lang/String;)V", false));
				body.add(new InsnNode(Opcodes.ATHROW));
				method.instructions = body;
				method.tryCatchBlocks.clear();
				method.localVariables = null;
				method.visibleLocalVariableAnnotations = null; // they point at labels of the old body
				method.invisibleLocalVariableAnnotations = null;
				method.maxStack = 3;
				method.maxLocals = Math.max(Type.getArgumentsAndReturnSizes(method.desc) >> 2, 1);
				stats[1]++;
				if (null != report) {
					report.println(node.name + "." + method.name + method.desc + "\t" + reason);
				}
				changed = true;
			}
		}
		if (!changed) {
			return bytes;
		}
		ClassWriter writer = new ClassWriter(0);
		node.accept(writer);
		return writer.toByteArray();
	}

	/** Classes in this jar that have a no-argument constructor, plus Object. */
	private static Set<String> noArgConstructors(ZipFile jar) throws Exception {
		Set<String> found = new HashSet<>(Set.of("java/lang/Object"));
		for (Enumeration<? extends ZipEntry> e = jar.entries(); e.hasMoreElements(); ) {
			ZipEntry entry = e.nextElement();
			if (!entry.getName().endsWith(".class") || entry.getName().startsWith("META-INF/")) {
				continue;
			}
			try (InputStream s = jar.getInputStream(entry)) {
				ClassNode node = new ClassNode();
				new ClassReader(s.readAllBytes()).accept(node, ClassReader.SKIP_CODE);
				if (node.methods.stream().anyMatch(m -> "<init>".equals(m.name) && "()V".equals(m.desc))) {
					found.add(node.name);
				}
			}
		}
		return found;
	}

	/**
	 * XStream builds Catroid classes that lack a no-argument constructor through Java serialization, which the browser
	 * does not have: it skips their constructors and only runs the no-argument one of the first non-Serializable
	 * superclass. This one runs the direct superclass's instead, so XStream finds a plain no-argument constructor and
	 * then sets the fields from the XML. The difference: superclass field initializers run, such as
	 * BrickBaseType.brickId, which XStream then overwrites when the XML has the field.
	 */
	private static boolean addNoArgConstructor(ClassNode node, Set<String> noArgConstructors) {
		if (!node.name.startsWith("org/catrobat/catroid/") || !noArgConstructors.contains(node.superName)
				|| 0 != (node.access & (Opcodes.ACC_INTERFACE | Opcodes.ACC_ABSTRACT | Opcodes.ACC_ENUM))
				|| noArgConstructors.contains(node.name)) {
			return false;
		}
		MethodNode constructor = new MethodNode(Opcodes.ACC_PUBLIC, "<init>", "()V", null, null);
		constructor.instructions.add(new VarInsnNode(Opcodes.ALOAD, 0));
		constructor.instructions.add(new MethodInsnNode(Opcodes.INVOKESPECIAL, node.superName, "<init>", "()V", false));
		constructor.instructions.add(new InsnNode(Opcodes.RETURN));
		constructor.maxStack = 1;
		constructor.maxLocals = 1;
		node.methods.add(constructor);
		return true;
	}

	/** Skips labels and line numbers: javac may put a line number between the ldc and its class$ call. */
	private static AbstractInsnNode previousInstruction(AbstractInsnNode insn) {
		AbstractInsnNode previous = insn.getPrevious();
		while (null != previous && previous.getOpcode() < 0) {
			previous = previous.getPrevious();
		}
		return previous;
	}

	private static boolean isClassLookup(MethodInsnNode call) {
		return Opcodes.INVOKESTATIC == call.getOpcode() && "class$".equals(call.name) && "(Ljava/lang/String;)Ljava/lang/Class;".equals(call.desc);
	}

	/** The missing method, field, class or Foo.class constant an instruction uses, or null. */
	private static String missingUse(AbstractInsnNode insn, Set<String> missing) {
		String[] uses = switch (insn) {
			case MethodInsnNode call -> new String[] {call.owner + "." + call.name + call.desc, call.owner};
			case FieldInsnNode field -> new String[] {field.owner + "." + field.name, field.owner};
			case TypeInsnNode type -> new String[] {type.desc};
			case LdcInsnNode ldc when ldc.cst instanceof Type t && Type.OBJECT == t.getSort() -> new String[] {t.getInternalName()};
			default -> new String[0];
		};
		for (String use : uses) {
			if (missing.contains(use)) {
				return use;
			}
		}
		return null;
	}

	/** Remapped names sit in the constant pool, so a byte search finds every class the remap changes. */
	private static boolean mentionsRemapped(byte[] bytes) {
		String text = new String(bytes, StandardCharsets.ISO_8859_1);
		return REMAP.keySet().stream().anyMatch(text::contains);
	}

	private static String receiver(String owner, String target) {
		return switch (target) {
			case "setContextClassLoader" -> "java/lang/Thread";
			case "getResources", "loadClass" -> "java/lang/ClassLoader";
			case "keys" -> "java/util/concurrent/ConcurrentHashMap";
			case "elapsedNow" -> "kotlin/time/TimeMark";
			case "getGenericSuperclass" -> "java/lang/Class";
			default -> owner;
		};
	}
}
