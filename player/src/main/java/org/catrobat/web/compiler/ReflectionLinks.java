package org.catrobat.web.compiler;

import java.lang.reflect.Constructor;
import java.lang.reflect.Field;
import org.teavm.dependency.AbstractDependencyListener;
import org.teavm.dependency.DependencyAgent;
import org.teavm.model.MethodReference;
import org.teavm.model.ValueType;
import org.teavm.vm.spi.TeaVMHost;
import org.teavm.vm.spi.TeaVMPlugin;

/**
 * TeaVM 0.15 emits a class's name, constructors, fields and enum constants only for classes that reach Class.getName(),
 * getDeclaredConstructors(), getDeclaredFields() and getEnumConstants(), and its reflection policy routes the classes it
 * finds by name there only if they are reached before Class.forName is. XStream gets most classes from its alias maps,
 * so it could neither find nor build them. This links every class the policy grants into those methods itself.
 */
public class ReflectionLinks extends AbstractDependencyListener implements TeaVMPlugin {
	private static final MethodReference[] REFLECTION = {new MethodReference(Class.class, "getName", String.class),
			new MethodReference(Class.class, "getDeclaredConstructors", Constructor[].class),
			new MethodReference(Class.class, "getDeclaredFields", Field[].class),
			new MethodReference(Class.class, "getEnumConstants", Object[].class)};

	@Override
	public void install(TeaVMHost host) {
		host.add(this);
	}

	@Override
	public void classReached(DependencyAgent agent, String className) {
		if (CatroidReflectionPolicy.isBuiltByXStream(className)) {
			for (MethodReference method : REFLECTION) {
				agent.linkMethod(method).getVariable(0).getClassValueNode().propagate(agent.getType(ValueType.object(className)));
			}
		}
	}
}
