package org.catrobat.web.compat;

import org.objectweb.asm.ClassReader;
import org.objectweb.asm.ClassWriter;
import org.objectweb.asm.Opcodes;
import org.objectweb.asm.Type;
import org.objectweb.asm.tree.AbstractInsnNode;
import org.objectweb.asm.tree.ClassNode;
import org.objectweb.asm.tree.InsnList;
import org.objectweb.asm.tree.InsnNode;
import org.objectweb.asm.tree.MethodInsnNode;
import org.objectweb.asm.tree.MethodNode;

import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.Enumeration;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;
import java.util.zip.ZipOutputStream;

/**
 * Turns the SDK's android.jar, whose methods all throw "Stub!", into one whose methods return default values, like
 * the Android Gradle plugin's mockable jar for unit tests. Only android/ classes are kept: the java.* copies must not
 * shadow TeaVM's class library. Usage: MockableJar sdk-android.jar out.jar
 */
public final class MockableJar {
	public static void main(String[] args) throws Exception {
		try (ZipFile in = new ZipFile(args[0]); ZipOutputStream out = new ZipOutputStream(Files.newOutputStream(Path.of(args[1])))) {
			for (Enumeration<? extends ZipEntry> e = in.entries(); e.hasMoreElements(); ) {
				ZipEntry entry = e.nextElement();
				if (!entry.getName().startsWith("android/") || !entry.getName().endsWith(".class")) {
					continue;
				}
				try (InputStream s = in.getInputStream(entry)) {
					out.putNextEntry(new ZipEntry(entry.getName()));
					out.write(mock(s.readAllBytes()));
					out.closeEntry();
				}
			}
		}
	}

	static byte[] mock(byte[] bytes) {
		ClassNode node = new ClassNode();
		new ClassReader(bytes).accept(node, 0);
		node.access &= ~Opcodes.ACC_FINAL;
		for (MethodNode method : node.methods) {
			method.access &= ~(Opcodes.ACC_FINAL | Opcodes.ACC_NATIVE);
			if (0 != (method.access & Opcodes.ACC_ABSTRACT)) {
				continue;
			}
			if ("<init>".equals(method.name)) {
				keepSuperCall(node, method);
				continue;
			}
			InsnList body = new InsnList();
			switch (Type.getReturnType(method.desc).getSort()) {
				case Type.VOID -> body.add(new InsnNode(Opcodes.RETURN));
				case Type.LONG -> {
					body.add(new InsnNode(Opcodes.LCONST_0));
					body.add(new InsnNode(Opcodes.LRETURN));
				}
				case Type.FLOAT -> {
					body.add(new InsnNode(Opcodes.FCONST_0));
					body.add(new InsnNode(Opcodes.FRETURN));
				}
				case Type.DOUBLE -> {
					body.add(new InsnNode(Opcodes.DCONST_0));
					body.add(new InsnNode(Opcodes.DRETURN));
				}
				case Type.OBJECT, Type.ARRAY -> {
					body.add(new InsnNode(Opcodes.ACONST_NULL));
					body.add(new InsnNode(Opcodes.ARETURN));
				}
				default -> {
					body.add(new InsnNode(Opcodes.ICONST_0));
					body.add(new InsnNode(Opcodes.IRETURN));
				}
			}
			method.instructions = body;
			method.tryCatchBlocks.clear();
			method.localVariables = null;
			method.maxStack = 2;
			method.maxLocals = Math.max(Type.getArgumentsAndReturnSizes(method.desc) >> 2, 1);
		}
		ClassWriter writer = new ClassWriter(ClassWriter.COMPUTE_MAXS);
		node.accept(writer);
		return writer.toByteArray();
	}

	/** A stub constructor keeps its own super(...) or this(...) call, which already verifies, and drops the throw. */
	private static void keepSuperCall(ClassNode node, MethodNode ctor) {
		AbstractInsnNode insn = ctor.instructions.getFirst();
		while (null != insn && !(insn instanceof MethodInsnNode call && Opcodes.INVOKESPECIAL == call.getOpcode()
				&& "<init>".equals(call.name) && (call.owner.equals(node.superName) || call.owner.equals(node.name)))) {
			insn = insn.getNext();
		}
		if (null == insn) {
			return;
		}
		while (null != insn.getNext()) {
			ctor.instructions.remove(insn.getNext());
		}
		ctor.instructions.add(new InsnNode(Opcodes.RETURN));
		ctor.tryCatchBlocks.clear();
		ctor.localVariables = null;
	}
}
