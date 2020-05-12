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


# Wrapper for the Crate Docs Utils

# `TOP_DIR` is the top-level directory of the repository
export TOP_DIR  := ..

# `DOCS_DIR` is the path to `docs` from `TOP_DIR`
export DOCS_DIR := docs


# Do not edit anything below this line
###############################################################################

UTILS_JSON := utils.json
UTILS_REPO := https://github.com/crate/crate-docs-utils.git
UTILS_DIR  := .utils
UTILS_MAKE := $(MAKE) -f $(UTILS_DIR)/src/rules.mk

# Parse the JSON file
UTILS_VERSION = $(shell cat $(UTILS_JSON) | \
    python -c 'import json, sys; print(json.load(sys.stdin)["message"])')

ifeq ($(UTILS_VERSION),)
$(error No version specified in $(UTILS_JSON))
endif

# Default rule
.PHONY: help
help: $(UTILS_DIR)
	@ $(UTILS_MAKE) $@

ifneq ($(wildcard $(TOP_DIR)/src/rules.mk),)
# The utils project detects itself and fakes an install of its own code so that
# it can test itself
$(UTILS_DIR):
	mkdir -p $@
	cp -R $(TOP_DIR)/src $@
else
# All other projects install a versioned copy of the utils
$(UTILS_DIR):
	git clone --depth=1 -c advice.detachedHead=false \
	    --branch=$(UTILS_VERSION) $(UTILS_REPO) $(UTILS_DIR)
endif

# Don't pass through this target
.PHONY: Makefile
Makefile:

# By default, pass targets through to the utils rules
.PHONY:
%: $(UTILS_DIR)
	@ $(UTILS_MAKE) $@

.PHONY: reset
reset:
	rm -rf $(UTILS_DIR)
