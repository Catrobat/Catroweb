package org.catrobat.web.compat;

import org.objectweb.asm.*;
import java.nio.file.*;
import java.util.*;
import java.util.zip.*;

/** Lists Foo.class constants and Class.forName("...") strings whose class exists in none of the given jars. */
/** Lists Foo.class and Class.forName("...") constants whose class exists in none of the jars; TeaVM's code generator crashes on them without saying which. */
public class FindLdc {
	public static void main(String[] args) throws Exception {
		Set<String> defined = new HashSet<>();
		Map<String, Set<String>> refs = new TreeMap<>();
		for (String jar : Files.readAllLines(Path.of(args[0]))) {
			if (jar.isBlank()) continue;
			try (ZipFile z = new ZipFile(jar)) {
				for (Enumeration<? extends ZipEntry> e = z.entries(); e.hasMoreElements(); ) {
					ZipEntry en = e.nextElement();
					String n = en.getName();
					if (!n.endsWith(".class")) continue;
					String cls = n.substring(0, n.length() - 6);
					if (cls.startsWith("org/teavm/classlib/")) {
						String simple = cls.substring(cls.lastIndexOf('/') + 1);
						String pkg = cls.substring("org/teavm/classlib/".length(), cls.lastIndexOf('/') + 1);
						if (simple.startsWith("T")) defined.add(pkg + simple.substring(1));
					}
					defined.add(cls);
					byte[] b = z.getInputStream(en).readAllBytes();
					new ClassReader(b).accept(new ClassVisitor(Opcodes.ASM9) {
						@Override public MethodVisitor visitMethod(int a, String name, String d, String s, String[] ex) {
							String where = cls + "." + name;
							return new MethodVisitor(Opcodes.ASM9) {
								String lastString;
								@Override public void visitLdcInsn(Object v) {
									if (v instanceof Type t && t.getSort() == Type.OBJECT) refs.computeIfAbsent(t.getInternalName(), k -> new TreeSet<>()).add(where);
									lastString = v instanceof String str ? str : null;
								}
								@Override public void visitMethodInsn(int op, String owner, String n, String d, boolean itf) {
									// Class.forName("a.b.C"): TeaVM resolves the constant like a class literal.
									if (null != lastString && "java/lang/Class".equals(owner) && "forName".equals(n) && lastString.matches("[\\w$.]+\\.[\\w$]+")) {
										refs.computeIfAbsent(lastString.replace('.', '/'), k -> new TreeSet<>()).add(where);
									}
									lastString = null;
								}
								@Override public void visitInsn(int op) { lastString = null; }
								@Override public void visitVarInsn(int op, int v) { lastString = null; }
							};
						}
					}, ClassReader.SKIP_DEBUG | ClassReader.SKIP_FRAMES);
				}
			}
		}
		refs.forEach((k, v) -> { if (!defined.contains(k)) System.out.println(k + "  <-  " + v.iterator().next() + (v.size() > 1 ? " (+" + (v.size() - 1) + ")" : "")); });
	}
}
