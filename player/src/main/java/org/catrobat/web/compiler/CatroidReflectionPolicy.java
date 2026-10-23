package org.catrobat.web.compiler;

import org.teavm.extension.Autoregistered;
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
		selectClasses(c -> null != c && null != c.name() && isModel(c.name()) && !c.name().contains(".actions."))
				.foundByName()
				.reflectableMembers(member -> true);
	}

	private static boolean isModel(String name) {
		for (String prefix : MODEL) {
			if (name.startsWith(prefix)) {
				return true;
			}
		}
		return false;
	}
}
