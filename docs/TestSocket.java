public class TestSocket {
	public static void main(String args) {
		Socket printer = new Socket("192.168.1.112",20000);
		BufferedWriter wr = new BufferedWriter(new OutputStreamWriter(printer.getOutputStream()));
		wr.write("aString#");
		wr.flush();
	}
}