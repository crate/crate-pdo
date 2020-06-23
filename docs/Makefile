# Licensed to Crate (https://crate.io) under one or more contributor license
# agreements.  See the NOTICE file distributed with this work for additional
# information regarding copyright ownership.  Crate licenses this file to you
# under the Apache License, Version 2.0 (the "License"); you may not use this
# file except in compliance with the License.  You may obtain a copy of the
# License at
#
#   http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
# License for the specific language governing permissions and limitations under
# the License.
#
# However, if you have executed another commercial license agreement with Crate
# these terms will supersede the license and you may use the software solely
# pursuant to the terms of the relevant commercial agreement.


# `TOP_DIR` is the top-level directory of the repository
export TOP_DIR  := ..

# `DOCS_DIR` is the path to `docs` from `TOP_DIR`
export DOCS_DIR := docs


# Do not edit anything below this line
###############################################################################

BUILD_JSON    := build.json
BUILD_REPO    := https://github.com/crate/crate-docs-build.git
CLONE_DIR     := .clone
SRC_DIR       := $(CLONE_DIR)/src
SELF_SRC      := $(TOP_DIR)/src
SELF_MAKEFILE := $(SELF_SRC)/rules.mk
SRC_MAKE      := $(MAKE) -f $(SRC_DIR)/rules.mk

# Parse the JSON file
BUILD_VERSION = $(shell cat $(BUILD_JSON) | \
    python -c 'import json, sys; print(json.load(sys.stdin)["message"])')

ifeq ($(BUILD_VERSION),)
$(error No version specified in $(BUILD_JSON))
endif

# Default rule
.PHONY: help
help: $(SRC_DIR)
	@ $(SRC_MAKE) $@

ifneq ($(wildcard $(SELF_MAKEFILE)),)
# The project detects itself and fakes an install of its own core build rules
# so that it can test itself
$(CLONE_DIR):
	mkdir -p $@

$(SRC_DIR): $(CLONE_DIR)
	cp -R $(SELF_SRC) $@
else
# All other projects install a versioned copy of the core build rules
$(SRC_DIR):
	git clone --depth=1 -c advice.detachedHead=false \
	    --branch=$(BUILD_VERSION) $(BUILD_REPO) $(CLONE_DIR)
endif

# Don't pass through this target
.PHONY: Makefile
Makefile:

# By default, pass targets through to the core build rules
.PHONY:
%: $(SRC_DIR)
	@ $(SRC_MAKE) $@

.PHONY: reset
reset:
	rm -rf $(CLONE_DIR)
