package org.catrobat.catroid.web;

import com.thoughtworks.xstream.XStream;
import com.thoughtworks.xstream.converters.Converter;
import com.thoughtworks.xstream.mapper.Mapper;

import org.catrobat.catroid.io.XstreamSerializer;
import org.junit.Test;

import java.io.PrintWriter;
import java.lang.reflect.Field;
import java.lang.reflect.Modifier;
import java.nio.file.Files;
import java.nio.file.Paths;

/** Run inside Catroid by prepare.sh (never committed there): dumps Catroid's XStream mapping as TSV so a web loader can be generated from it. */
public class MappingExportTest {
	@Test
	public void export() throws Exception {
		XStream x = XstreamSerializer.getInstance().getXstream();
		Mapper m = x.getMapper();
		try (PrintWriter out = new PrintWriter(System.getenv("MAPPING_OUT"))) {
			for (String name : Files.readAllLines(Paths.get(System.getenv("MAPPING_CLASSES")))) {
				Class<?> c;
				try {
					c = Class.forName(name);
				} catch (Throwable t) {
					out.println("ERROR\t" + name + "\t" + t);
					continue;
				}
				if (c.isInterface() || c.isEnum()) {
					continue;
				}
				String conv;
				try {
					conv = x.getConverterLookup().lookupConverterForType(c).getClass().getSimpleName();
				} catch (Throwable t) {
					conv = "none";
				}
				out.println("CLASS\t" + name + "\t" + m.serializedClass(c) + "\t" + conv
						+ "\t" + (c.getSuperclass() == null ? "" : c.getSuperclass().getName()));
				for (Field f : c.getDeclaredFields()) {
					int mod = f.getModifiers();
					if (Modifier.isStatic(mod) || Modifier.isTransient(mod) || !m.shouldSerializeMember(c, f.getName())) {
						continue;
					}
					try {
					out.println("FIELD\t" + name + "\t" + f.getName() + "\t" + m.serializedMember(c, f.getName())
							+ "\t" + f.getGenericType().getTypeName()
							+ "\t" + (m.getConverterFromAttribute(c, f.getName(), f.getType()) != null ? "attr" : "elem")
							+ "\t" + (m.getImplicitCollectionDefForFieldName(c, f.getName()) != null ? "implicit" : ""));
					} catch (Throwable t) {
						out.println("FIELDERROR\t" + name + "\t" + f.getName() + "\t" + t);
					}
				}
			}
		}
	}
}
