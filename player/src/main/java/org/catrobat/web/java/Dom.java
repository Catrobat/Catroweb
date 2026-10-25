package org.catrobat.web.java;

import org.teavm.jso.JSBody;

import java.util.ArrayList;
import java.util.List;

/**
 * The part of org.w3c.dom that BackwardCompatibleCatrobatLanguageXStream.modifyXMLToSupportUnknownFields uses, over
 * the browser's DOM: compat/Rewrite remaps org.w3c.dom to these types in that class only, and redirects its
 * getDocument to {@link #edited}. As in Android's DOM, node lists are snapshots and renameNode renames in place.
 */
public final class Dom {
	private Dom() {
	}

	private static Document edited;

	/** The document modifyXMLToSupportUnknownFields edits next (instead of reading code.xml from a file). */
	public static void edit(org.teavm.jso.dom.xml.Document document) {
		removeWhitespaceText(document);
		edited = new BrowserDocument(document);
	}

	public static Document edited() {
		return edited;
	}

	/** What getDocument's normalizeWhiteSpaces does with XPath: drops the text nodes that are only whitespace. */
	@JSBody(params = "document", script = "var walker = document.createTreeWalker(document, 4), blank = [];"
			+ "while (walker.nextNode()) if (walker.currentNode.nodeValue.trim() === '') blank.push(walker.currentNode);"
			+ "blank.forEach(function (node) { node.parentNode.removeChild(node); });")
	private static native void removeWhitespaceText(org.teavm.jso.dom.xml.Document document);

	public interface Node {
		String getNodeName();

		String getNodeValue();

		String getNamespaceURI();

		String getTextContent();

		void setTextContent(String text);

		Node getParentNode();

		Node getFirstChild();

		NodeList getChildNodes();

		boolean hasChildNodes();

		NamedNodeMap getAttributes();

		Node appendChild(Node child);

		Node removeChild(Node child);

		Node replaceChild(Node newChild, Node oldChild);
	}

	public interface Element extends Node {
		String getAttribute(String name);

		void setAttribute(String name, String value);

		void setAttributeNS(String namespace, String name, String value);

		boolean hasAttribute(String name);
	}

	public interface Attr extends Node {
		String getName();

		String getValue();
	}

	public interface Document extends Node {
		Element getDocumentElement();

		Element createElement(String name);

		NodeList getElementsByTagName(String name);

		Node renameNode(Node node, String namespace, String name);
	}

	public interface NodeList {
		int getLength();

		Node item(int index);
	}

	public interface NamedNodeMap {
		int getLength();

		Node item(int index);

		Node getNamedItem(String name);
	}

	private static Node wrap(org.teavm.jso.dom.xml.Node node) {
		if (null == node) {
			return null;
		}
		return switch (node.getNodeType()) {
			case org.teavm.jso.dom.xml.Node.ELEMENT_NODE -> new BrowserElement(node);
			case org.teavm.jso.dom.xml.Node.ATTRIBUTE_NODE -> new BrowserAttr(node);
			default -> new BrowserNode(node);
		};
	}

	private static org.teavm.jso.dom.xml.Node unwrap(Node node) {
		return ((BrowserNode) node).node;
	}

	private static NodeList snapshot(org.teavm.jso.dom.xml.NodeList<? extends org.teavm.jso.dom.xml.Node> live) {
		List<Node> nodes = new ArrayList<>();
		for (int i = 0; i < live.getLength(); i++) {
			nodes.add(wrap(live.item(i)));
		}
		return new NodeList() {
			@Override
			public int getLength() {
				return nodes.size();
			}

			@Override
			public Node item(int index) {
				return index < nodes.size() ? nodes.get(index) : null;
			}
		};
	}

	private static class BrowserNode implements Node {
		org.teavm.jso.dom.xml.Node node; // renameNode replaces it

		BrowserNode(org.teavm.jso.dom.xml.Node node) {
			this.node = node;
		}

		@Override
		public String getNodeName() {
			return node.getNodeName();
		}

		@Override
		public String getNodeValue() {
			return node.getNodeValue();
		}

		@Override
		public String getNamespaceURI() {
			return node.getNamespaceURI();
		}

		@Override
		public String getTextContent() {
			return node.getTextContent();
		}

		@Override
		public void setTextContent(String text) {
			node.setTextContent(text);
		}

		@Override
		public Node getParentNode() {
			return wrap(node.getParentNode());
		}

		@Override
		public Node getFirstChild() {
			return wrap(node.getFirstChild());
		}

		@Override
		public NodeList getChildNodes() {
			return snapshot(node.getChildNodes());
		}

		@Override
		public boolean hasChildNodes() {
			return node.hasChildNodes();
		}

		@Override
		public NamedNodeMap getAttributes() {
			return null; // as org.w3c.dom for anything but an element
		}

		@Override
		public Node appendChild(Node child) {
			node.appendChild(unwrap(child));
			return child;
		}

		@Override
		public Node removeChild(Node child) {
			node.removeChild(unwrap(child));
			return child;
		}

		@Override
		public Node replaceChild(Node newChild, Node oldChild) {
			node.replaceChild(unwrap(newChild), unwrap(oldChild));
			return oldChild;
		}
	}

	private static final class BrowserElement extends BrowserNode implements Element {
		BrowserElement(org.teavm.jso.dom.xml.Node node) {
			super(node);
		}

		private org.teavm.jso.dom.xml.Element element() {
			return (org.teavm.jso.dom.xml.Element) node;
		}

		@Override
		public String getAttribute(String name) {
			String value = element().getAttribute(name);
			return null == value ? "" : value; // org.w3c.dom gives "" for a missing attribute
		}

		@Override
		public void setAttribute(String name, String value) {
			element().setAttribute(name, value);
		}

		@Override
		public void setAttributeNS(String namespace, String name, String value) {
			element().setAttributeNS(namespace, name, value);
		}

		@Override
		public boolean hasAttribute(String name) {
			return element().hasAttribute(name);
		}

		@Override
		public NamedNodeMap getAttributes() {
			org.teavm.jso.dom.xml.NamedNodeMap<org.teavm.jso.dom.xml.Attr> attributes = node.getAttributes();
			return new NamedNodeMap() {
				@Override
				public int getLength() {
					return attributes.getLength();
				}

				@Override
				public Node item(int index) {
					return wrap(attributes.item(index));
				}

				@Override
				public Node getNamedItem(String name) {
					return wrap(attributes.getNamedItem(name));
				}
			};
		}
	}

	private static final class BrowserAttr extends BrowserNode implements Attr {
		BrowserAttr(org.teavm.jso.dom.xml.Node node) {
			super(node);
		}

		@Override
		public String getName() {
			return ((org.teavm.jso.dom.xml.Attr) node).getName();
		}

		@Override
		public String getValue() {
			return ((org.teavm.jso.dom.xml.Attr) node).getValue();
		}
	}

	private static final class BrowserDocument extends BrowserNode implements Document {
		BrowserDocument(org.teavm.jso.dom.xml.Document document) {
			super(document);
		}

		private org.teavm.jso.dom.xml.Document document() {
			return (org.teavm.jso.dom.xml.Document) node;
		}

		@Override
		public Element getDocumentElement() {
			return (Element) wrap(document().getDocumentElement());
		}

		@Override
		public Element createElement(String name) {
			return (Element) wrap(document().createElement(name));
		}

		@Override
		public NodeList getElementsByTagName(String name) {
			return snapshot(document().getElementsByTagName(name));
		}

		/** The browser cannot rename an element: a new one takes its attributes, children and place. */
		@Override
		public Node renameNode(Node renamed, String namespace, String name) {
			org.teavm.jso.dom.xml.Element old = (org.teavm.jso.dom.xml.Element) unwrap(renamed);
			org.teavm.jso.dom.xml.Element element = document().createElementNS(namespace, name);
			org.teavm.jso.dom.xml.NamedNodeMap<org.teavm.jso.dom.xml.Attr> attributes = old.getAttributes();
			for (int i = 0; i < attributes.getLength(); i++) {
				element.setAttributeNS(attributes.item(i).getNamespaceURI(), attributes.item(i).getName(), attributes.item(i).getValue());
			}
			while (null != old.getFirstChild()) {
				element.appendChild(old.getFirstChild());
			}
			if (null != old.getParentNode()) {
				old.getParentNode().replaceChild(element, old);
			}
			((BrowserNode) renamed).node = element; // in place, as far as the caller's node goes
			return renamed;
		}
	}
}
