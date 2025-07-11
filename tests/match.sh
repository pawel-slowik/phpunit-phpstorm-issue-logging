#!/bin/sh

# Check whether a file's content matches a template.
#
# The template can contain the special sequence %d, which matches a non-empty
# sequence of ASCII digits in the data file. No other special sequences are
# recognized.
#
# How it works: the idea is to compare the files byte by byte and obtain a
# machine readable representation of the differences. The list of differences
# is then filtered using a fixed pattern representing expected / acceptable
# changes resulting from the special sequence. If all the differences match the
# pattern, the file is considered to match the template.
#
# For example, with the following template:
#
# version: %d
# time: 0.%d s
# memory: %d MB
#
# and data file contents:
#
# version: 1
# time: 0.003 s
# memory: 14 MB
#
# $change_lines looks like this:
#
# -%d
# +1
# -%d
# +003
# -%d
# +14
#
# $changes then looks like this:
#
# -%d +1
# -%d +003
# -%d +14
#
# which can be filtered with a regular expression.

if [ $# -ne 2 ]; then
	echo "usage: $0 data_filename template_filename"
	exit 2
fi

data="$1"
template="$2"

git_diff=$(git diff --no-index --no-exit-code --word-diff=porcelain --word-diff-regex=. "$template" "$data")
git_diff_exit_status=$?
if [ $git_diff_exit_status -eq 0 ]; then
	exit 0
fi
if [ $git_diff_exit_status -ne 1 ]; then
	echo "diff failed" 1>&2
	exit 2
fi
if [ "$git_diff" = "" ]; then
	echo "diff failed" 1>&2
	exit 2
fi

if ! git_diff_without_header=$(echo "$git_diff" | tail -n +5); then
	echo "diff header removal failed" 1>&2
	exit 2
fi

if ! change_lines=$(echo "$git_diff_without_header" | grep -v -E '^[ ~@]'); then
	echo "change extraction failed" 1>&2
	exit 2
fi

if ! changes=$(echo "$change_lines" | paste -d ' ' - -); then
	echo "change folding failed" 1>&2
	exit 2
fi

recognized_changes=$(echo "$changes" | grep -E '^-%d [+][0-9]+$')
filter_exit_status=$?
if [ $filter_exit_status -ne 0 ] && [ $filter_exit_status -ne 1 ]; then
	echo "change filtering failed" 1>&2
	exit 2
fi

if [ "$changes" = "$recognized_changes" ]; then
	exit 0
fi

exit 1
