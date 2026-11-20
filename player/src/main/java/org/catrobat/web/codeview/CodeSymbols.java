package org.catrobat.web.codeview;

import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scope;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.formulaeditor.InternToken;
import org.catrobat.catroid.formulaeditor.UserData;
import org.catrobat.catroid.formulaeditor.UserDataWrapper;

import java.util.ArrayList;
import java.util.IdentityHashMap;
import java.util.List;
import java.util.Map;

/** Declared names and the identities of the selections and tokens that refer to them. */
final class CodeSymbols {
	private final Project project;
	private final Map<Object, Symbol> byObject = new IdentityHashMap<>();
	private final List<Symbol> declarations = new ArrayList<>();
	private Scope scope;
	private int sprites;

	CodeSymbols(Project project) {
		this.project = project;
		register(project.getUserVariables(), "variable", "project");
		register(project.getMultiplayerVariables(), "variable", "project");
		register(project.getUserLists(), "list", "project");
	}

	void enterSprite(Sprite sprite) {
		scope = new Scope(project, sprite, null);
		String owner = "sprite-" + sprites++;
		register(sprite.getUserVariables(), "variable", owner);
		register(sprite.getUserLists(), "list", owner);
	}

	private void register(List<? extends UserData<?>> items, String kind, String owner) {
		for (UserData<?> item : items) {
			if (item != null && item.getName() != null && !byObject.containsKey(item)) {
				Symbol symbol = new Symbol("name-" + declarations.size(), kind, owner, item.getName());
				declarations.add(symbol);
				byObject.put(item, symbol);
			}
		}
	}

	Symbol resolve(InternToken token) {
		if (scope == null) {
			return null;
		}
		String name = token.getTokenStringValue();
		Object item = switch (token.getInternTokenType()) {
			case USER_VARIABLE -> UserDataWrapper.getUserVariable(name, scope);
			case USER_LIST -> UserDataWrapper.getUserList(name, scope);
			default -> null;
		};
		return byObject.get(item);
	}

	void addChoice(CodeExport.Json json, Object item, String displayed) {
		Symbol symbol = byObject.get(item);
		if (symbol != null && displayed.equals(symbol.name())) {
			CodeExport.Json segments = new CodeExport.Json().open('[');
			symbol.writeSegment(segments, displayed);
			json.key("segments").raw(segments.close(']'));
		}
	}

	void writeDeclarations(CodeExport.Json json) {
		json.key("symbols").open('[');
		for (Symbol symbol : declarations) {
			json.open('{').key("id").value(symbol.id()).key("kind").value(symbol.kind())
					.key("scope").value(symbol.scope()).key("name").value(symbol.name()).close('}');
		}
		json.close(']');
	}

	record Symbol(String id, String kind, String scope, String name) {
		void writeSegment(CodeExport.Json json, String displayed) {
			json.open('{').key("kind").value(kind).key("text").value(displayed)
					.key("source").value(name).key("symbol").value(id).close('}');
		}
	}
}
