package org.catrobat.web.java;

/**
 * java.io.ObjectStreamField, which TeaVM's class library lacks (compat/Rewrite remaps it). XStream's
 * SerializationMembers only builds an empty array of it; Java serialization itself is not available on the web.
 */
public final class ObjectStreamField {
	private ObjectStreamField() {
	}
}
