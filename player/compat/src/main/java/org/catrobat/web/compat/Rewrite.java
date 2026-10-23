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

import java.io.InputStream;
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
			Map.entry("java/lang/Class.getGenericSuperclass()Ljava/lang/reflect/Type;", "getGenericSuperclass"));

	static java.io.PrintStream REPORT;

	public static void main(String[] args) throws Exception {
		if (null != System.getenv("STUB_REPORT")) {
			REPORT = new java.io.PrintStream(new java.io.FileOutputStream(System.getenv("STUB_REPORT"), true), true);
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
					bytes = rewrite(bytes, missing, stats);
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

	static byte[] rewrite(byte[] bytes, Set<String> missing, int[] stats) {
		ClassNode node = new ClassNode();
		new ClassReader(bytes).accept(new ClassRemapper(node, new SimpleRemapper(REMAP)), 0);
		boolean changed = !java.util.Objects.equals(node.superName, new ClassReader(bytes).getSuperName()) || touches(new ClassReader(bytes));
		if (node.interfaces.removeIf(missing::contains)) {
			changed = true; // the class works without claiming a type the browser lacks
		}
		if (null != node.superName && missing.contains(node.superName)) {
			node.superName = "java/lang/Object";
			changed = true;
		}
		for (MethodNode method : node.methods) {
			boolean stub = false;
			String reason = null;
			if (0 != (method.access & Opcodes.ACC_NATIVE) && missing.contains(node.name + "." + method.name + method.desc)) {
				method.access &= ~Opcodes.ACC_NATIVE; // JNI code has no browser counterpart
				stub = true;
				reason = "native";
			}
			for (AbstractInsnNode insn = method.instructions.getFirst(); null != insn; insn = insn.getNext()) {
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
					} else if (missing.contains(call.owner + "." + call.name + call.desc) || missing.contains(call.owner)) {
						stub = true;
						reason = null == reason ? call.owner + "." + call.name + call.desc : reason;
					}
				} else if (insn instanceof FieldInsnNode field && (missing.contains(field.owner + "." + field.name) || missing.contains(field.owner))) {
					stub = true;
					reason = null == reason ? field.owner + "." + field.name : reason;
				} else if (insn instanceof TypeInsnNode type && missing.contains(type.desc)) {
					stub = true;
					reason = null == reason ? type.desc : reason;
				} else if (insn instanceof LdcInsnNode ldc && ldc.cst instanceof Type t && Type.OBJECT == t.getSort() && missing.contains(t.getInternalName())) {
					stub = true; // Foo.class of a missing Foo
					reason = null == reason ? t.getInternalName() + ".class" : reason;
				}
			}
			if (stub && 0 == (method.access & Opcodes.ACC_ABSTRACT)) {
				InsnList body = new InsnList();
				body.add(new TypeInsnNode(Opcodes.NEW, "java/lang/UnsupportedOperationException"));
				body.add(new InsnNode(Opcodes.DUP));
				body.add(new LdcInsnNode("Not available on the web: " + node.name + "." + method.name));
				body.add(new MethodInsnNode(Opcodes.INVOKESPECIAL, "java/lang/UnsupportedOperationException", "<init>", "(Ljava/lang/String;)V", false));
				body.add(new InsnNode(Opcodes.ATHROW));
				method.instructions = body;
				method.tryCatchBlocks.clear();
				method.localVariables = null;
				method.maxStack = 3;
				method.maxLocals = Math.max(Type.getArgumentsAndReturnSizes(method.desc) >> 2, 1);
				stats[1]++;
				if (null != REPORT) {
					REPORT.println(node.name + "." + method.name + method.desc + "\t" + reason);
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

	private static boolean touches(ClassReader reader) {
		String text = new String(reader.b, java.nio.charset.StandardCharsets.ISO_8859_1);
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
