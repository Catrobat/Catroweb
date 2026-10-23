package org.catrobat.web.compat;

/** RFC 1321 MD5, because TeaVM has no MessageDigest. Only used for UUID.nameUUIDFromBytes. */
final class Md5 {
	private static final int[] S = {7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22, 5, 9, 14, 20, 5, 9, 14, 20,
			5, 9, 14, 20, 5, 9, 14, 20, 4, 11, 16, 23, 4, 11, 16, 23, 4, 11, 16, 23, 4, 11, 16, 23, 6, 10, 15, 21, 6, 10,
			15, 21, 6, 10, 15, 21, 6, 10, 15, 21};
	private static final int[] K = new int[64];

	static {
		for (int i = 0; i < 64; i++) {
			K[i] = (int) (long) (Math.abs(Math.sin(i + 1)) * 4294967296.0);
		}
	}

	private Md5() {
	}

	static byte[] digest(byte[] message) {
		int blocks = ((message.length + 8) >>> 6) + 1;
		byte[] padded = new byte[blocks << 6];
		System.arraycopy(message, 0, padded, 0, message.length);
		padded[message.length] = (byte) 0x80;
		long bits = (long) message.length << 3;
		for (int i = 0; i < 8; i++) {
			padded[padded.length - 8 + i] = (byte) (bits >>> (8 * i));
		}
		int a0 = 0x67452301;
		int b0 = 0xefcdab89;
		int c0 = 0x98badcfe;
		int d0 = 0x10325476;
		int[] m = new int[16];
		for (int block = 0; block < blocks; block++) {
			for (int j = 0; j < 16; j++) {
				int o = (block << 6) + (j << 2);
				m[j] = (padded[o] & 0xff) | (padded[o + 1] & 0xff) << 8 | (padded[o + 2] & 0xff) << 16 | (padded[o + 3] & 0xff) << 24;
			}
			int a = a0;
			int b = b0;
			int c = c0;
			int d = d0;
			for (int i = 0; i < 64; i++) {
				int f;
				int g;
				if (i < 16) {
					f = (b & c) | (~b & d);
					g = i;
				} else if (i < 32) {
					f = (d & b) | (~d & c);
					g = (5 * i + 1) & 15;
				} else if (i < 48) {
					f = b ^ c ^ d;
					g = (3 * i + 5) & 15;
				} else {
					f = c ^ (b | ~d);
					g = (7 * i) & 15;
				}
				int next = d;
				d = c;
				c = b;
				b = b + Integer.rotateLeft(a + f + K[i] + m[g], S[i]);
				a = next;
			}
			a0 += a;
			b0 += b;
			c0 += c;
			d0 += d;
		}
		byte[] out = new byte[16];
		int[] words = {a0, b0, c0, d0};
		for (int i = 0; i < 16; i++) {
			out[i] = (byte) (words[i >> 2] >>> (8 * (i & 3)));
		}
		return out;
	}
}
