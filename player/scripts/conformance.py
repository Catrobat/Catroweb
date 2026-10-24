"""Compares a conformance run with conformance.tsv.
Usage: conformance.py <results.tsv> <conformance.tsv> [--update | --regressions]

results.tsv: <project path>, tab, data-test-result="…" or nothing. conformance.tsv: <project path>, tab, pass/fail,
tab, why (the assertion message, "no result", …). A test that passes in conformance.tsv must still pass; tests that
newly pass are listed so conformance.tsv can be raised with --update. --regressions only prints the failing ones' paths,
for conformance.sh to retry."""
import html
import re
import sys
from pathlib import Path


def outcome(raw):
    found = re.search(r'data-test-result="([^"]*)"', raw)
    result = html.unescape(found[1]) if found else ""
    if result.startswith("7777"):
        return "pass", ""
    reason = " ".join(result.split()[1:]) if result else "no result"
    return "fail", (reason[:160] + "…") if len(reason) > 160 else reason


def main(results_file, baseline_file, update=False, regressions_only=False):
    results = {}
    for line in Path(results_file).read_text().splitlines():
        name, _, raw = line.partition("\t")
        results[name] = outcome(raw)
    passed = sum(1 for status, _ in results.values() if "pass" == status)
    if not regressions_only:
        print(f"Conformance: {passed} of {len(results)} Catroid test projects pass on the stage")
    baseline = {}
    if Path(baseline_file).exists():
        for line in Path(baseline_file).read_text().splitlines():
            name, status, *_ = line.split("\t")
            baseline[name] = status
    if update:
        for name in sorted(n for n, s in baseline.items() if "pass" == s and results.get(n, ("fail",))[0] != "pass"):
            print(f"  no longer marked as passing: {name}: {results.get(name, ('', 'missing'))[1]}")
        Path(baseline_file).write_text("".join(f"{n}\t{s}\t{r}\n" for n, (s, r) in sorted(results.items())))
        return 0
    regressions = [n for n, s in baseline.items() if "pass" == s and results.get(n, ("fail", "missing"))[0] != "pass"]
    if regressions_only:
        for name in regressions:
            print(name)
        return 0
    improved = [n for n, (s, _) in results.items() if "pass" == s and baseline.get(n) != "pass"]
    for name in regressions:
        print(f"  no longer passes: {name}: {results.get(name, ('', 'missing'))[1]}")
    for name in improved:
        print(f"  now passes (raise with --update): {name}")
    return 1 if regressions else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1], sys.argv[2], "--update" in sys.argv[3:], "--regressions" in sys.argv[3:]))
