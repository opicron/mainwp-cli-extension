# mainwp-cli-extension
Extend MainWP CLI commands

## NAME
  wp mainwp delete_actions

## DESCRIPTION
  remove non-mainwp actions from child sites

## SYNOPSIS
  wp mainwp delete_actions [<websiteid>] [--all]

## OPTIONS
  [<websiteid>]
    The id (or ids, comma separated) of the child sites that need to be synced.

  [--all]
    If set, non-mainwp actions from all child sites will be removed

## EXAMPLES
    wp mainwp delete_action 2,5
    wp mainwp delete_action --all

Synopsis [<websiteid>] [--all]

