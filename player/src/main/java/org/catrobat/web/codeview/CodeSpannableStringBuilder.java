package org.catrobat.web.codeview;

import android.text.SpannableStringBuilder;

/**
 * The SDK's SpannableStringBuilder with its text (the mockable one's toString is null): Rewrite substitutes it in
 * Catroid's code, where FormulaSpannableStringBuilder builds the formula fields' text with it. Spans (the colour
 * squares) are dropped.
 */
public class CodeSpannableStringBuilder extends SpannableStringBuilder {
	private final StringBuilder text = new StringBuilder();

	public CodeSpannableStringBuilder() {
	}

	public CodeSpannableStringBuilder(CharSequence text) {
		this.text.append(text);
	}

	public CodeSpannableStringBuilder(CharSequence text, int start, int end) {
		this.text.append(text, start, end);
	}

	@Override
	public SpannableStringBuilder append(CharSequence s) {
		text.append(s);
		return this;
	}

	@Override
	public SpannableStringBuilder append(CharSequence s, int start, int end) {
		text.append(s, start, end);
		return this;
	}

	@Override
	public SpannableStringBuilder append(char c) {
		text.append(c);
		return this;
	}

	@Override
	public SpannableStringBuilder append(CharSequence s, Object what, int flags) {
		text.append(s);
		return this;
	}

	@Override
	public void setSpan(Object what, int start, int end, int flags) {
	}

	@Override
	public int length() {
		return text.length();
	}

	@Override
	public char charAt(int index) {
		return text.charAt(index);
	}

	@Override
	public CharSequence subSequence(int start, int end) {
		return text.subSequence(start, end);
	}

	@Override
	public String toString() {
		return text.toString();
	}
}
