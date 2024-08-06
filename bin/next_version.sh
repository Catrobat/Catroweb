#!/bin/bash

# Extract the current version from the .env file
current_version=$(grep -oP "(?<=APP_VERSION=')[0-9]{2}\.[0-9]{1,2}\.[0-9]{1,5}" .env)
IFS=' ' read -r major minor patch <<< "$(echo $current_version | awk -F'.' '{print $1, $2, $3}')"

# Now you can use $year, $month, and $day
#echo "current version: $current_version"
#echo "major (year): $major"
#echo "minor (month): $minor"
#echo "patch: $patch"

# Increment the patch version
new_patch=$((patch + 1))

# Get current year and month
current_year=$(date +%y)
current_month=$(date +%-m)

# If the current year or month is different, reset the patch version
if [[ $major -ne $current_year || $minor -ne $current_month ]]; then
  new_patch=0
fi

# Formulate the new version
new_version="${current_year}.${current_month}.${new_patch}"

# Output the new version
#echo "new version: $new_version"
echo $new_version