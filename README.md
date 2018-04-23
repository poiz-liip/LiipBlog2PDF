# LiipBlog2PDF
A Plugin that enables Wordpress Site Administrators or Editors to export Blog-Posts to PDF at the click of a Button from the Backend using the DomPDF Library.
The Plugin adds an **Export this Post to PDF** Button to the right of the Add Media Button.
Clicking the aforementioned Button generates a PDF File from the Blog Post. 
Currently, this Plugin only renders the Content of the WP Editor (including Images) along with the Post's Banner as well as a tiny section of the Footer.

Generated PDF Files are dumped inside the ***wp-content/uploads/blog_pdf/pdf*** Folder.
Assuming the the post_name for the Blog is: "dental-hygiene-for-children"; the generated PDF would be found at the Location:
***wp-content/uploads/blog_pdf/pdf/dental-hygiene-for-children.pdf*** additionally, a raw HTML Mark-Up is equally generated and dumped
in the Folder: ***wp-content/uploads/blog_pdf/html/dental-hygiene-for-children.html***

And by the way; one can also choose to (or not to) generate the HTML File -  its use here was only for Debugging...
