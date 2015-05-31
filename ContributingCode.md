# SVN Commit Access #

Commit access will be granted to active developers who demonstrate an ability to contribute substantial, high-quality, and well-tested code to the project.

If you wish to contribute code but do not have commit access, check out the code anonymously, and submit a patch when you are done.

# Creating a patch #

From the top directory of your Plans code checkout, do
```
svn diff > my_change.patch
```
You can attach the resulting file to the relevant ticket in the issue tracker with a comment explaining what the code you're submitting does. Your patch will be reviewed by someone with commit access and if we like it, we'll include it in the project.

If you have added files as part of your change, do
```
svn add file1 file2
```
before you generate the diff.

If you would like to only generate a patch for certain files or directories, you can do
```
svn diff folder1 file1.txt > my_change.patch
```