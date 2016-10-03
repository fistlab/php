# Contribution Guide

## Bug Reports

When you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.    

A bug report may also be sent in the form of a pull request containing a failing test.

## Branching

All bug fixes should be sent to the latest stable branch. Bug fixes should never be sent to the master branch unless they fix features that exist only in the upcoming release.

Minor features that are fully backwards compatible with the current Fistlab release may be sent to the latest stable branch.

Major new features should always be sent to the master branch, which contains the upcoming Fistlab release.

If you are unsure if your feature qualifies as a major or minor, please ask Mark Topper at mark@ulties.com.

## Coding Style

Fistlab follows the PSR-2 coding standard and the PSR-4 autoloading standard.
