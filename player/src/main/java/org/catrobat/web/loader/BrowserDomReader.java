package org.catrobat.web.loader;

import com.thoughtworks.xstream.io.naming.NameCoder;
import com.thoughtworks.xstream.io.xml.AbstractDocumentReader;
import com.thoughtworks.xstream.io.xml.XmlFriendlyNameCoder;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;
import org.teavm.jso.core.JSArrayReader;
import org.teavm.jso.dom.xml.Attr;
import org.teavm.jso.dom.xml.Element;
import org.teavm.jso.dom.xml.Node;
import org.teavm.jso.dom.xml.NodeList;


/**
 * Lets Catroid's own XStream read a document parsed by the browser's DOMParser: XStream's DomReader needs the
 * JDK's org.w3c.dom, which TeaVM does not have. Mirrors DomReader.
 */
public class BrowserDomReader extends AbstractDocumentReader {
	private Element current;
	private JSArrayReader<Element> children;
	private JSObject listed;

	public BrowserDomReader(Element root) {
		super(root, new UncloneableNameCoder(new XmlFriendlyNameCoder()));
	}

	/** XStream clones Cloneable name coders by reflection, which TeaVM cannot do; this one it keeps as is. */
	private record UncloneableNameCoder(NameCoder coder) implements NameCoder {
		@Override
		public String encodeNode(String name) {
			return coder.encodeNode(name);
		}

		@Override
		public String encodeAttribute(String name) {
			return coder.encodeAttribute(name);
		}

		@Override
		public String decodeNode(String name) {
			return coder.decodeNode(name);
		}

		@Override
		public String decodeAttribute(String name) {
			return coder.decodeAttribute(name);
		}
	}

	@Override
	protected void reassignCurrentElement(Object element) {
		// Called from the super constructor, before field initializers would run: no initializer for listed either.
		if (null == listed) {
			listed = newMap();
		}
		current = (Element) element;
		children = elementChildren(listed, current);
	}

	/**
	 * XStream comes back to a parent after each of its children, so this reader lists a parent's element children
	 * once: listing them each time made large projects quadratic (a list of thousands of bricks). Per reader, as the
	 * loader edits the document between two readings of an old project.
	 */
	@JSBody(script = "return new Map();")
	private static native JSObject newMap();

	@JSBody(params = {"listed", "element"}, script = "var c = listed.get(element);"
			+ "if (!c) { c = []; for (var n = element.firstChild; n; n = n.nextSibling) if (n.nodeType === 1) c.push(n);"
			+ " listed.set(element, c); }"
			+ "return c;")
	private static native JSArrayReader<Element> elementChildren(JSObject listed, Element element);

	@Override
	protected Object getParent() {
		return current.getParentNode();
	}

	@Override
	protected Object getChild(int index) {
		return children.get(index);
	}

	@Override
	protected int getChildCount() {
		return children.getLength();
	}

	@Override
	public String getNodeName() {
		return decodeNode(current.getNodeName());
	}

	@Override
	public String getValue() {
		StringBuilder text = new StringBuilder();
		NodeList<Node> nodes = current.getChildNodes();
		for (int i = 0; i < nodes.getLength(); i++) {
			Node node = nodes.item(i);
			if (Node.TEXT_NODE == node.getNodeType() || Node.CDATA_SECTION_NODE == node.getNodeType()) {
				text.append(node.getNodeValue());
			}
		}
		return text.toString();
	}

	@Override
	public String getAttribute(String name) {
		Attr attribute = current.getAttributeNode(encodeAttribute(name)); // getAttribute() says "" when absent
		return null == attribute ? null : attribute.getValue();
	}

	@Override
	public String getAttribute(int index) {
		return current.getAttributes().item(index).getValue();
	}

	@Override
	public int getAttributeCount() {
		return current.getAttributes().getLength();
	}

	@Override
	public String getAttributeName(int index) {
		return decodeAttribute(current.getAttributes().item(index).getName());
	}
}
