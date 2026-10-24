package org.catrobat.web.java;

import java.io.IOException;

/**
 * java.io.CharConversionException, which TeaVM's class library lacks (compat/Rewrite remaps it). opencsv's CSVReader
 * lists it among the exceptions it passes through; without it the class cannot load and the CSV bricks fail.
 */
public class CharConversionException extends IOException {
	public CharConversionException() {
	}

	public CharConversionException(String message) {
		super(message);
	}
}
